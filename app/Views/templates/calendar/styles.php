<style>
/* Dropdown specific styles */
#dropdownContainer {
  position: relative;
  z-index: 100;
}

#viewDropdownMenu {
  position: absolute !important;
  z-index: 9999 !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  /* Mobile improvements */
  min-width: 140px;
}

/* Ensure parent containers don't create stacking context issues */
.main-content {
  position: relative;
  z-index: 1;
}

/* Ensure header container allows dropdown to overflow */
.mx-auto.w-full.max-w-6xl.bg-white.rounded-xl.shadow.overflow-hidden {
  overflow: visible !important;
}

/* Header container overflow fix */
.flex.items-center.justify-between.px-6.py-4.border-b,
.flex.flex-col.sm\\:flex-row.sm\\:items-center.sm\\:justify-between {
  overflow: visible !important;
}

/* Mobile-responsive slide-in panel */
.slide-in-panel {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 100vw;
  background: #fff;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  z-index: 50;
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  overflow-y: auto;
  padding: 1rem;
}

/* Tablet and desktop styles */
@media (min-width: 640px) {
  .slide-in-panel {
    width: 28rem;
    max-width: 100vw;
    padding: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .slide-in-panel {
    padding: 2rem;
  }
}

.slide-in-panel.active {
  transform: translateX(0);
}

/* Close button styling - mobile-friendly */
.close-btn {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #888;
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  z-index: 10;
  /* Touch-friendly size */
  min-width: 44px;
  min-height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 640px) {
  .close-btn {
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    padding: 0.5rem;
  }
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

/* Panel content padding to account for close button */
.slide-in-panel > h5,
.slide-in-panel > form,
.slide-in-panel > div:not(.close-btn) {
  padding-top: 3rem;
}

@media (min-width: 640px) {
  .slide-in-panel > h5,
  .slide-in-panel > form,
  .slide-in-panel > div:not(.close-btn) {
    padding-top: 3.5rem;
  }
}

/* Improved mobile calendar */
@media (max-width: 767px) {
  /* Make calendar cells square and touch-friendly */
  #monthView td[data-date] {
    touch-action: manipulation;
    position: relative;
  }
  
  /* Better calendar table spacing for mobile */
  #monthView table {
    border-collapse: separate;
    border-spacing: 1px;
    background-color: #e5e7eb;
  }
  
  #monthView td, #monthView th {
    background-color: white;
    border: none;
  }
  
  /* Mobile dropdown improvements */
  #viewDropdownMenu {
    right: 0;
    left: auto;
    width: 160px;
  }
  
  /* Ensure appointment indicators are visible */
  .appointment-indicator {
    min-width: 16px;
    min-height: 16px;
    font-size: 10px;
  }
}

/* Desktop calendar improvements */
@media (min-width: 768px) {
  #monthView td[data-date] {
    /* Desktop uses fixed height rectangular cells */
    touch-action: manipulation;
    position: relative;
  }
  
  #monthView td:hover {
    background-color: #eff6ff !important;
    transition: background-color 0.2s ease;
  }
  
  /* Better table spacing for desktop */
  #monthView table {
    border-collapse: collapse;
    background-color: transparent;
  }
  
  #monthView td, #monthView th {
    border: 1px solid #e5e7eb;
  }
}

/* Large desktop calendar */
@media (min-width: 1024px) {
  #monthView td[data-date] {
    /* Maintain rectangular layout for large screens */
  }
}

/* Touch improvements for all interactive elements */
button, select, input[type="time"], input[type="date"], textarea {
  touch-action: manipulation;
}

/* Ensure proper scroll behavior on mobile */
@media (max-width: 767px) {
  .slide-in-panel {
    -webkit-overflow-scrolling: touch;
  }
  
  /* Prevent zoom on input focus on iOS */
  input, select, textarea {
    font-size: 16px;
  }
}

/* Calendar cell improvements */
#monthView td {
  vertical-align: top;
  position: relative;
}

#monthView td[data-date]:not(.cursor-not-allowed):hover {
  background-color: #dbeafe;
}

/* Appointment count badge styling */
.appointment-count-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
  transition: all 0.2s ease;
}

.appointment-count-badge:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

/* Loading and error states for mobile */
.loading-state, .error-state {
  padding: 1rem;
  text-align: center;
  font-size: 14px;
}

@media (min-width: 640px) {
  .loading-state, .error-state {
    padding: 1.5rem;
    font-size: 16px;
  }
}

/* Today highlight */
#monthView td[data-date].today {
  background-color: #fef3c7 !important;
  border: 2px solid #f59e0b;
}

#monthView td[data-date].today .text-gray-700 {
  color: #92400e !important;
  font-weight: 600;
}
</style> 