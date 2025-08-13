/**
 * Conditions Analyzer - Analyzes dental conditions and generates summaries
 * Handles condition parsing, statistics, and summary generation
 */

class ConditionsAnalyzer {
    constructor() {
        this.conditionConfig = this.initializeConditionConfig();
    }

    // ==================== CONFIGURATION ====================

    initializeConditionConfig() {
        return {
            'healthy': { icon: '✅', label: 'Healthy', color: '#10B981', priority: 1 },
            'cavity': { icon: '🕳️', label: 'Cavity', color: '#EF4444', priority: 9 },
            'filled': { icon: '🔧', label: 'Filled', color: '#F59E0B', priority: 3 },
            'crown': { icon: '👑', label: 'Crown', color: '#8B5CF6', priority: 4 },
            'root_canal': { icon: '🔵', label: 'Root Canal', color: '#3B82F6', priority: 7 },
            'fractured': { icon: '💥', label: 'Fractured', color: '#F97316', priority: 8 },
            'loose': { icon: '🔄', label: 'Loose', color: '#F59E0B', priority: 6 },
            'sensitive': { icon: '⚡', label: 'Sensitive', color: '#EAB308', priority: 5 },
            'bleeding': { icon: '🩸', label: 'Bleeding', color: '#DC2626', priority: 7 },
            'swollen': { icon: '🔴', label: 'Swollen', color: '#EC4899', priority: 6 },
            'impacted': { icon: '⛔', label: 'Impacted', color: '#8B4513', priority: 8 },
            'missing': { icon: '❌', label: 'Missing', color: '#6B7280', priority: 10 }
        };
    }

    // ==================== ANALYSIS METHODS ====================

    analyzeConditions(groupedData) {
        const analysis = {
            totalTeeth: 0,
            conditions: {},
            criticalIssues: [],
            lastCheckup: null,
            teethWithIssues: 0,
            healthyTeeth: 0
        };

        let latestDate = null;
        
        Object.keys(groupedData).forEach(toothNumber => {
            const toothData = groupedData[toothNumber];
            
            if (toothData && toothData.length > 0) {
                analysis.totalTeeth++;
                const latestRecord = toothData[0];
                const condition = latestRecord.condition || 'unknown';
                
                // Count conditions
                if (!analysis.conditions[condition]) {
                    analysis.conditions[condition] = { count: 0, teeth: [] };
                }
                analysis.conditions[condition].count++;
                analysis.conditions[condition].teeth.push(toothNumber);
                
                // Track latest checkup date
                const recordDate = new Date(latestRecord.created_at);
                if (!latestDate || recordDate > latestDate) {
                    latestDate = recordDate;
                }
                
                // Categorize issues
                if (this.isCriticalCondition(condition)) {
                    analysis.criticalIssues.push({
                        tooth: toothNumber,
                        condition: condition,
                        date: latestRecord.created_at,
                        notes: latestRecord.notes
                    });
                }
                
                if (condition === 'healthy') {
                    analysis.healthyTeeth++;
                } else {
                    analysis.teethWithIssues++;
                }
            }
        });
        
        analysis.lastCheckup = latestDate;
        return analysis;
    }

    isCriticalCondition(condition) {
        const criticalConditions = ['cavity', 'fractured', 'root_canal', 'bleeding', 'impacted', 'loose'];
        return criticalConditions.includes(condition);
    }

    // ==================== SUMMARY GENERATION ====================

    generateSummary(groupedData) {
        const analysis = this.analyzeConditions(groupedData);
        const summaryHtml = this.createSummaryHTML(analysis);
        this.updateSummaryDisplay(summaryHtml);
        return analysis;
    }

    createSummaryHTML(analysis) {
        const totalTeeth = analysis.totalTeeth;
        const healthyPercentage = totalTeeth > 0 ? Math.round((analysis.healthyTeeth / totalTeeth) * 100) : 0;
        
        return `
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                    Dental Condition Summary
                </h3>
                
                ${this.createOverviewSection(analysis, healthyPercentage)}
                ${this.createConditionsBreakdown(analysis)}
                ${this.createCriticalIssuesSection(analysis)}
                ${this.createLastCheckupInfo(analysis)}
            </div>
        `;
    }

    createOverviewSection(analysis, healthyPercentage) {
        const statusClass = healthyPercentage >= 80 ? 'text-green-600' : 
                           healthyPercentage >= 60 ? 'text-yellow-600' : 'text-red-600';
        
        return `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-blue-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600">${analysis.totalTeeth}</div>
                    <div class="text-sm text-blue-700">Total Teeth</div>
                </div>
                <div class="bg-green-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">${analysis.healthyTeeth}</div>
                    <div class="text-sm text-green-700">Healthy</div>
                </div>
                <div class="bg-red-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-red-600">${analysis.teethWithIssues}</div>
                    <div class="text-sm text-red-700">Need Attention</div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Overall Health</span>
                    <span class="text-sm font-medium ${statusClass}">${healthyPercentage}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-red-400 via-yellow-400 to-green-500 h-2 rounded-full transition-all duration-500" 
                         style="width: ${healthyPercentage}%"></div>
                </div>
            </div>
        `;
    }

    createConditionsBreakdown(analysis) {
        const sortedConditions = Object.entries(analysis.conditions)
            .sort(([,a], [,b]) => {
                const priorityA = this.conditionConfig[a]?.priority || 5;
                const priorityB = this.conditionConfig[b]?.priority || 5;
                return priorityB - priorityA;
            });

        if (sortedConditions.length === 0) {
            return '<div class="text-gray-500 text-center py-4">No dental records available</div>';
        }

        const conditionsHtml = sortedConditions
            .map(([condition, data]) => {
                const config = this.conditionConfig[condition] || { 
                    icon: '❓', 
                    label: condition, 
                    color: '#6B7280' 
                };
                
                const teethList = data.teeth.length <= 3 ? 
                    data.teeth.join(', ') : 
                    `${data.teeth.slice(0, 3).join(', ')} +${data.teeth.length - 3} more`;
                
                return `
                    <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg">${config.icon}</span>
                            <div>
                                <div class="font-medium text-gray-800">${config.label}</div>
                                <div class="text-xs text-gray-500">Teeth: ${teethList}</div>
                            </div>
                        </div>
                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            ${data.count}
                        </span>
                    </div>
                `;
            })
            .join('');

        return `
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Conditions Breakdown</h4>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    ${conditionsHtml}
                </div>
            </div>
        `;
    }

    createCriticalIssuesSection(analysis) {
        if (analysis.criticalIssues.length === 0) {
            return `
                <div class="mb-4 p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-700 font-medium">No critical issues detected</span>
                    </div>
                </div>
            `;
        }

        const criticalHtml = analysis.criticalIssues
            .slice(0, 5) // Show only first 5 critical issues
            .map(issue => {
                const config = this.conditionConfig[issue.condition] || { 
                    icon: '⚠️', 
                    label: issue.condition 
                };
                
                return `
                    <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                        <div class="flex items-center space-x-2">
                            <span>${config.icon}</span>
                            <span class="font-medium text-red-800">Tooth ${issue.tooth}</span>
                            <span class="text-red-600">${config.label}</span>
                        </div>
                        <span class="text-xs text-red-500">
                            ${new Date(issue.date).toLocaleDateString()}
                        </span>
                    </div>
                `;
            })
            .join('');

        const moreIssues = analysis.criticalIssues.length > 5 ? 
            `<div class="text-xs text-red-500 mt-2">+${analysis.criticalIssues.length - 5} more critical issues</div>` : '';

        return `
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-red-700 mb-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Critical Issues (${analysis.criticalIssues.length})
                </h4>
                <div class="space-y-2">
                    ${criticalHtml}
                    ${moreIssues}
                </div>
            </div>
        `;
    }

    createLastCheckupInfo(analysis) {
        if (!analysis.lastCheckup) {
            return `
                <div class="text-xs text-gray-500 text-center p-2 bg-gray-50 rounded">
                    <i class="fas fa-calendar-times mr-1"></i>
                    No checkup date available
                </div>
            `;
        }

        const lastCheckupDate = analysis.lastCheckup.toLocaleDateString();
        const daysSince = Math.floor((new Date() - analysis.lastCheckup) / (1000 * 60 * 60 * 24));
        
        let timeStatus = '';
        let statusClass = 'text-gray-600';
        
        if (daysSince <= 30) {
            timeStatus = 'Recent';
            statusClass = 'text-green-600';
        } else if (daysSince <= 180) {
            timeStatus = `${Math.floor(daysSince/30)} months ago`;
            statusClass = 'text-yellow-600';
        } else {
            timeStatus = `${Math.floor(daysSince/365)} years ago`;
            statusClass = 'text-red-600';
        }

        return `
            <div class="text-xs ${statusClass} text-center p-2 bg-gray-50 rounded">
                <i class="fas fa-calendar-check mr-1"></i>
                Last checkup: ${lastCheckupDate} (${timeStatus})
            </div>
        `;
    }

    // ==================== DISPLAY METHODS ====================

    updateSummaryDisplay(summaryHtml) {
        const summaryContainer = document.getElementById('dentalConditionsSummary');
        if (summaryContainer) {
            summaryContainer.innerHTML = summaryHtml;
            console.log('✅ Conditions summary updated');
        } else {
            console.warn('⚠️ Summary container not found');
        }
    }

    // ==================== UTILITY METHODS ====================

    getConditionInfo(condition) {
        return this.conditionConfig[condition] || { 
            icon: '❓', 
            label: condition, 
            color: '#6B7280', 
            priority: 5 
        };
    }

    getConditionIcon(condition) {
        return this.conditionConfig[condition]?.icon || '❓';
    }

    getConditionLabel(condition) {
        return this.conditionConfig[condition]?.label || condition;
    }

    getConditionColor(condition) {
        return this.conditionConfig[condition]?.color || '#6B7280';
    }

    // ==================== EXPORT METHODS ====================

    generateTextSummary(groupedData) {
        const analysis = this.analyzeConditions(groupedData);
        
        let summary = `DENTAL CONDITION SUMMARY\n`;
        summary += `=======================\n\n`;
        summary += `Total Teeth Examined: ${analysis.totalTeeth}\n`;
        summary += `Healthy Teeth: ${analysis.healthyTeeth}\n`;
        summary += `Teeth with Issues: ${analysis.teethWithIssues}\n\n`;
        
        if (Object.keys(analysis.conditions).length > 0) {
            summary += `CONDITION BREAKDOWN:\n`;
            summary += `-------------------\n`;
            
            Object.entries(analysis.conditions)
                .sort(([,a], [,b]) => b.count - a.count)
                .forEach(([condition, data]) => {
                    const config = this.getConditionInfo(condition);
                    summary += `${config.label}: ${data.count} teeth (${data.teeth.join(', ')})\n`;
                });
        }
        
        if (analysis.criticalIssues.length > 0) {
            summary += `\nCRITICAL ISSUES:\n`;
            summary += `---------------\n`;
            analysis.criticalIssues.forEach(issue => {
                const config = this.getConditionInfo(issue.condition);
                summary += `Tooth ${issue.tooth}: ${config.label} (${new Date(issue.date).toLocaleDateString()})\n`;
            });
        }
        
        if (analysis.lastCheckup) {
            summary += `\nLast Checkup: ${analysis.lastCheckup.toLocaleDateString()}\n`;
        }
        
        return summary;
    }

    generateJSONSummary(groupedData) {
        const analysis = this.analyzeConditions(groupedData);
        return JSON.stringify(analysis, null, 2);
    }
}

// Export for use
window.ConditionsAnalyzer = ConditionsAnalyzer;
