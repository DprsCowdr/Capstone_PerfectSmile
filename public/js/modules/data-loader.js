/**
 * Data Loader - Handles all API calls and data fetching
 * Centralized data loading with proper error handling
 */

class DataLoader {
    constructor(baseUrl) {
        this.baseUrl = baseUrl || '';
    }

    // ==================== COMMON FETCH WRAPPER ====================

    async fetchWithErrorHandling(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            ...options
        };

        try {
            console.log(`🔍 Fetching: ${url}`);
            
            const response = await fetch(url, defaultOptions);
            console.log(`📊 Response status: ${response.status}`);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Expected JSON response, got: ${contentType}. Response: ${text.substring(0, 200)}...`);
            }
            
            const data = await response.json();
            console.log('✅ API response received:', data);
            
            return data;
        } catch (error) {
            console.error(`❌ Error fetching ${url}:`, error);
            throw error;
        }
    }

    // ==================== PATIENT DATA LOADING ====================

    async loadPatientInfo(patientId) {
        const url = `${this.baseUrl}/admin/patient-info/${patientId}`;
        return await this.fetchWithErrorHandling(url);
    }

    async loadDentalRecords(patientId) {
        const url = `${this.baseUrl}/admin/patient-dental-records/${patientId}`;
        return await this.fetchWithErrorHandling(url);
    }

    async loadDentalChart(patientId) {
        console.log(`🔍 Loading dental chart for patient ID: ${patientId}`);
        console.log(`📡 API URL: ${this.baseUrl}/admin/patient-dental-chart/${patientId}`);
        
        const url = `${this.baseUrl}/admin/patient-dental-chart/${patientId}`;
        return await this.fetchWithErrorHandling(url);
    }

    async loadAppointments(patientId) {
        console.log('Loading appointments for patient:', patientId);
        console.log('Base URL:', this.baseUrl);
        
        const url = `${this.baseUrl}/admin/patient-appointments/${patientId}`;
        console.log('Fetching from URL:', url);
        
        return await this.fetchWithErrorHandling(url);
    }

    async loadTreatments(patientId) {
        const url = `${this.baseUrl}/admin/patient-treatments/${patientId}`;
        return await this.fetchWithErrorHandling(url);
    }

    async loadMedicalRecords(patientId) {
        console.log('Loading medical records for patient:', patientId);
        console.log('Base URL:', this.baseUrl);
        
        const url = `${this.baseUrl}/admin/patient-medical-records/${patientId}`;
        console.log('Fetching from URL:', url);
        
        return await this.fetchWithErrorHandling(url);
    }

    // ==================== RECORD MANAGEMENT ====================

    async deleteRecord(recordId) {
        const url = `${this.baseUrl}/admin/dental/delete-record/${recordId}`;
        const options = {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        return await this.fetchWithErrorHandling(url, options);
    }

    async updatePatientNotes(patientId, notes) {
        const url = `${this.baseUrl}/admin/patient-notes/${patientId}`;
        const options = {
            method: 'POST',
            body: JSON.stringify({ notes })
        };

        return await this.fetchWithErrorHandling(url, options);
    }

    // ==================== BATCH DATA LOADING ====================

    async loadAllPatientData(patientId) {
        try {
            console.log(`🔄 Loading all data for patient ${patientId}`);
            
            const [
                patientInfo,
                dentalRecords,
                dentalChart,
                appointments,
                treatments,
                medicalRecords
            ] = await Promise.allSettled([
                this.loadPatientInfo(patientId),
                this.loadDentalRecords(patientId),
                this.loadDentalChart(patientId),
                this.loadAppointments(patientId),
                this.loadTreatments(patientId),
                this.loadMedicalRecords(patientId)
            ]);

            return {
                patientInfo: patientInfo.status === 'fulfilled' ? patientInfo.value : null,
                dentalRecords: dentalRecords.status === 'fulfilled' ? dentalRecords.value : null,
                dentalChart: dentalChart.status === 'fulfilled' ? dentalChart.value : null,
                appointments: appointments.status === 'fulfilled' ? appointments.value : null,
                treatments: treatments.status === 'fulfilled' ? treatments.value : null,
                medicalRecords: medicalRecords.status === 'fulfilled' ? medicalRecords.value : null,
                errors: {
                    patientInfo: patientInfo.status === 'rejected' ? patientInfo.reason : null,
                    dentalRecords: dentalRecords.status === 'rejected' ? dentalRecords.reason : null,
                    dentalChart: dentalChart.status === 'rejected' ? dentalChart.reason : null,
                    appointments: appointments.status === 'rejected' ? appointments.reason : null,
                    treatments: treatments.status === 'rejected' ? treatments.reason : null,
                    medicalRecords: medicalRecords.status === 'rejected' ? medicalRecords.reason : null
                }
            };
        } catch (error) {
            console.error('❌ Error loading batch patient data:', error);
            throw error;
        }
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Check if the API endpoint is available
     */
    async checkApiHealth() {
        try {
            const url = `${this.baseUrl}/admin/health-check`;
            const response = await fetch(url, { method: 'HEAD' });
            return response.ok;
        } catch (error) {
            console.warn('API health check failed:', error);
            return false;
        }
    }

    /**
     * Get base URL for the API
     */
    getApiUrl(endpoint) {
        return `${this.baseUrl}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
    }

    /**
     * Cache management for frequently accessed data
     */
    static cache = new Map();
    
    async loadWithCache(cacheKey, loadFunction, ttl = 300000) { // 5 minutes default TTL
        const cached = DataLoader.cache.get(cacheKey);
        const now = Date.now();
        
        if (cached && (now - cached.timestamp) < ttl) {
            console.log(`📦 Using cached data for: ${cacheKey}`);
            return cached.data;
        }
        
        console.log(`🔄 Loading fresh data for: ${cacheKey}`);
        const data = await loadFunction();
        
        DataLoader.cache.set(cacheKey, {
            data,
            timestamp: now
        });
        
        return data;
    }

    clearCache(pattern = null) {
        if (pattern) {
            // Clear specific pattern
            for (const key of DataLoader.cache.keys()) {
                if (key.includes(pattern)) {
                    DataLoader.cache.delete(key);
                }
            }
        } else {
            // Clear all cache
            DataLoader.cache.clear();
        }
        console.log(`🗑️ Cache cleared${pattern ? ` for pattern: ${pattern}` : ''}`);
    }
}

// Export for use
window.DataLoader = DataLoader;
