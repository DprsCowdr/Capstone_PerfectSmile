/**
 * Records Utilities - Helper functions and utilities for dental records
 * Common utility functions, formatters, validators, and helpers
 */

class RecordsUtilities {
    constructor() {
        this.toothNames = this.initializeToothNames();
    }

    // ==================== TOOTH NAMING ====================

    initializeToothNames() {
        return {
            // Adult teeth (permanent dentition) - Universal Numbering System
            1: "Upper Right Third Molar (Wisdom Tooth)",
            2: "Upper Right Second Molar", 
            3: "Upper Right First Molar",
            4: "Upper Right Second Premolar",
            5: "Upper Right First Premolar", 
            6: "Upper Right Canine",
            7: "Upper Right Lateral Incisor",
            8: "Upper Right Central Incisor",
            9: "Upper Left Central Incisor",
            10: "Upper Left Lateral Incisor",
            11: "Upper Left Canine",
            12: "Upper Left First Premolar",
            13: "Upper Left Second Premolar",
            14: "Upper Left First Molar",
            15: "Upper Left Second Molar",
            16: "Upper Left Third Molar (Wisdom Tooth)",
            17: "Lower Left Third Molar (Wisdom Tooth)",
            18: "Lower Left Second Molar",
            19: "Lower Left First Molar",
            20: "Lower Left Second Premolar",
            21: "Lower Left First Premolar",
            22: "Lower Left Canine",
            23: "Lower Left Lateral Incisor",
            24: "Lower Left Central Incisor",
            25: "Lower Right Central Incisor",
            26: "Lower Right Lateral Incisor",
            27: "Lower Right Canine",
            28: "Lower Right First Premolar",
            29: "Lower Right Second Premolar",
            30: "Lower Right First Molar",
            31: "Lower Right Second Molar",
            32: "Lower Right Third Molar (Wisdom Tooth)"
        };
    }

    getToothName(toothNumber) {
        const num = parseInt(toothNumber);
        return this.toothNames[num] || `Tooth ${num}`;
    }

    getToothQuadrant(toothNumber) {
        const num = parseInt(toothNumber);
        if (num >= 1 && num <= 8) return "Upper Right";
        if (num >= 9 && num <= 16) return "Upper Left";
        if (num >= 17 && num <= 24) return "Lower Left";
        if (num >= 25 && num <= 32) return "Lower Right";
        return "Unknown";
    }

    getToothType(toothNumber) {
        const num = parseInt(toothNumber);
        const position = ((num - 1) % 8) + 1;
        
        switch (position) {
            case 1: case 8: return "Third Molar (Wisdom)";
            case 2: case 7: return "Second Molar";
            case 3: case 6: return "First Molar";
            case 4: case 5: return "Premolar";
            default: return "Unknown";
        }
    }

    // ==================== DATE FORMATTING ====================

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (error) {
            console.warn('Error formatting date:', dateString, error);
            return 'Invalid Date';
        }
    }

    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.warn('Error formatting datetime:', dateString, error);
            return 'Invalid Date';
        }
    }

    getRelativeTime(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
            if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} months ago`;
            return `${Math.floor(diffInSeconds / 31536000)} years ago`;
        } catch (error) {
            console.warn('Error calculating relative time:', dateString, error);
            return 'Unknown';
        }
    }

    // ==================== TEXT FORMATTING ====================

    truncateText(text, maxLength = 100) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    capitalizeFirstLetter(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    formatConditionName(condition) {
        if (!condition) return 'Unknown';
        return condition.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // ==================== VALIDATION ====================

    isValidToothNumber(toothNumber) {
        const num = parseInt(toothNumber);
        return num >= 1 && num <= 32;
    }

    isValidCondition(condition) {
        const validConditions = [
            'healthy', 'cavity', 'filled', 'crown', 'root_canal',
            'fractured', 'loose', 'sensitive', 'bleeding', 
            'swollen', 'impacted', 'missing'
        ];
        return validConditions.includes(condition);
    }

    isValidDate(dateString) {
        if (!dateString) return false;
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }

    // ==================== DATA MANIPULATION ====================

    sortRecordsByDate(records, ascending = false) {
        if (!Array.isArray(records)) return [];
        
        return records.sort((a, b) => {
            const dateA = new Date(a.created_at);
            const dateB = new Date(b.created_at);
            return ascending ? dateA - dateB : dateB - dateA;
        });
    }

    groupRecordsByDate(records) {
        if (!Array.isArray(records)) return {};
        
        const grouped = {};
        records.forEach(record => {
            const date = this.formatDate(record.created_at);
            if (!grouped[date]) {
                grouped[date] = [];
            }
            grouped[date].push(record);
        });
        
        return grouped;
    }

    filterRecordsByCondition(records, condition) {
        if (!Array.isArray(records)) return [];
        return records.filter(record => record.condition === condition);
    }

    getUniqueConditions(records) {
        if (!Array.isArray(records)) return [];
        const conditions = records.map(record => record.condition).filter(Boolean);
        return [...new Set(conditions)];
    }

    // ==================== ERROR HANDLING ====================

    safelyExecute(fn, fallback = null, context = 'operation') {
        try {
            return fn();
        } catch (error) {
            console.error(`Error in ${context}:`, error);
            return fallback;
        }
    }

    logError(error, context = 'Unknown') {
        console.error(`[${context}] Error:`, {
            message: error.message,
            stack: error.stack,
            timestamp: new Date().toISOString()
        });
    }

    // ==================== LOCAL STORAGE ====================

    saveToLocalStorage(key, data) {
        try {
            localStorage.setItem(key, JSON.stringify(data));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    loadFromLocalStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Error loading from localStorage:', error);
            return defaultValue;
        }
    }

    clearLocalStorage(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Error clearing localStorage:', error);
            return false;
        }
    }

    // ==================== URL HELPERS ====================

    buildUrl(baseUrl, endpoint, params = {}) {
        let url = `${baseUrl.replace(/\/$/, '')}/${endpoint.replace(/^\//, '')}`;
        
        const queryParams = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                queryParams.append(key, value);
            }
        });
        
        const queryString = queryParams.toString();
        return queryString ? `${url}?${queryString}` : url;
    }

    getBaseUrl() {
        return `${window.location.protocol}//${window.location.host}${window.location.pathname.split('/').slice(0, -1).join('/')}`;
    }

    // ==================== SEARCH AND FILTER ====================

    searchRecords(records, searchTerm) {
        if (!Array.isArray(records) || !searchTerm) return records;
        
        const term = searchTerm.toLowerCase();
        return records.filter(record => {
            return (
                (record.condition && record.condition.toLowerCase().includes(term)) ||
                (record.notes && record.notes.toLowerCase().includes(term)) ||
                (record.tooth_number && record.tooth_number.toString().includes(term)) ||
                (record.created_at && this.formatDate(record.created_at).toLowerCase().includes(term))
            );
        });
    }

    filterRecordsByDateRange(records, startDate, endDate) {
        if (!Array.isArray(records)) return [];
        
        const start = startDate ? new Date(startDate) : null;
        const end = endDate ? new Date(endDate) : null;
        
        return records.filter(record => {
            const recordDate = new Date(record.created_at);
            
            if (start && recordDate < start) return false;
            if (end && recordDate > end) return false;
            
            return true;
        });
    }

    // ==================== EXPORT HELPERS ====================

    generateCSV(records) {
        if (!Array.isArray(records) || records.length === 0) {
            return 'No data to export';
        }
        
        const headers = ['Date', 'Tooth Number', 'Tooth Name', 'Condition', 'Notes'];
        const csvRows = [headers.join(',')];
        
        records.forEach(record => {
            const row = [
                this.formatDate(record.created_at),
                record.tooth_number,
                this.getToothName(record.tooth_number),
                this.formatConditionName(record.condition),
                `"${(record.notes || '').replace(/"/g, '""')}"`
            ];
            csvRows.push(row.join(','));
        });
        
        return csvRows.join('\n');
    }

    downloadFile(content, filename, mimeType = 'text/plain') {
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // ==================== ANIMATION HELPERS ====================

    fadeIn(element, duration = 300) {
        if (!element) return;
        
        element.style.opacity = '0';
        element.style.display = 'block';
        
        const start = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = progress;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    fadeOut(element, duration = 300) {
        if (!element) return;
        
        const start = performance.now();
        const startOpacity = parseFloat(getComputedStyle(element).opacity) || 1;
        
        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            element.style.opacity = startOpacity * (1 - progress);
            
            if (progress >= 1) {
                element.style.display = 'none';
            } else {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    // ==================== DEBOUNCE/THROTTLE ====================

    debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // ==================== UTILITY CONSTANTS ====================

    getConstants() {
        return {
            TOOTH_NUMBERS: Array.from({ length: 32 }, (_, i) => i + 1),
            CONDITIONS: [
                'healthy', 'cavity', 'filled', 'crown', 'root_canal',
                'fractured', 'loose', 'sensitive', 'bleeding', 
                'swollen', 'impacted', 'missing'
            ],
            QUADRANTS: ['Upper Right', 'Upper Left', 'Lower Left', 'Lower Right'],
            DATE_FORMATS: {
                SHORT: 'MM/dd/yyyy',
                LONG: 'MMMM dd, yyyy',
                ISO: 'yyyy-MM-dd'
            }
        };
    }
}

// Export for use
window.RecordsUtilities = RecordsUtilities;
