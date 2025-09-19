# Enhanced availableSlots() API Documentation

## Overview
The `availableSlots()` endpoint has been enhanced to provide comprehensive slot metadata for easier frontend consumption, including ownership tracking, blocking information, and separated available/unavailable slot arrays.

## Request
**POST** `/appointments/availableSlots`

```json
{
    "date": "2024-01-15",
    "branch_id": 1,
    "service_id": 2,
    "dentist_id": 3  // optional
}
```

## Enhanced Response Structure

```json
{
    "success": true,
    "slots": [
        // Backward compatibility: only available slots (existing frontend can still use this)
        {
            "time": "9:00 AM",
            "timestamp": 1705327200,
            "datetime": "2024-01-15 09:00:00",
            "available": true,
            "duration_minutes": 60,
            "grace_minutes": 15,
            "ends_at": "10:15 AM",
            "dentist_id": 3
        }
    ],
    "all_slots": [
        // Complete slot information (available + unavailable)
        {
            "time": "9:00 AM",
            "timestamp": 1705327200,
            "datetime": "2024-01-15 09:00:00", 
            "available": true,
            "duration_minutes": 60,
            "grace_minutes": 15,
            "ends_at": "10:15 AM",
            "dentist_id": 3
        },
        {
            "time": "10:00 AM",
            "timestamp": 1705330800,
            "datetime": "2024-01-15 10:00:00",
            "available": false,
            "duration_minutes": 60,
            "grace_minutes": 15,
            "ends_at": "11:15 AM",
            "dentist_id": 3,
            "blocking_info": {
                "type": "appointment",
                "start": "10:00 AM",
                "end": "11:00 AM",
                "appointment_id": 123,
                "owned_by_current_user": false
            },
            "owned_by_current_user": false
        }
    ],
    "available_slots": [
        // Only available slots (same structure as all_slots but filtered)
    ],
    "unavailable_slots": [
        // Only unavailable slots with blocking information
    ],
    "occupied_map": [
        // Enhanced occupied time ranges with ownership tracking
        [1705330800, 1705334400, 123, 5, true]  // [start, end, appointment_id, user_id, owned_by_current_user]
    ],
    "metadata": {
        "total_slots_checked": 32,
        "available_count": 20,
        "unavailable_count": 12,
        "duration_minutes": 60,
        "grace_minutes": 15,
        "day_start": "8:00 AM",
        "day_end": "5:00 PM"
    }
}
```

## Key Features

### 1. Ownership Tracking
- `owned_by_current_user`: Boolean flag indicating if the blocking appointment belongs to the current user
- Allows frontend to show different UI for user's own appointments vs others
- Available in both slot objects and blocking_info

### 2. Rich Blocking Information
- `blocking_info.type`: "appointment" or "availability_block"
- `blocking_info.start/end`: Human-readable time range
- `blocking_info.appointment_id`: Reference to blocking appointment
- `blocking_info.owned_by_current_user`: Ownership flag

### 3. Separated Arrays
- `available_slots`: Only bookable slots
- `unavailable_slots`: Only blocked slots with reasons
- `all_slots`: Complete picture for timeline views

### 4. Enhanced Metadata
- Total slot counts for statistics
- Duration and grace period info
- Day operating hours

## Frontend Usage Examples

### Basic Booking Form (Backward Compatible)
```javascript
// Existing code continues to work
response.slots.forEach(slot => {
    addSlotOption(slot.time, slot.timestamp);
});
```

### Advanced Timeline View
```javascript
response.all_slots.forEach(slot => {
    const slotElement = createSlotElement(slot);
    
    if (!slot.available) {
        slotElement.classList.add('blocked');
        
        if (slot.owned_by_current_user) {
            slotElement.classList.add('owned-by-me');
            slotElement.title = `Your appointment: ${slot.blocking_info.start} - ${slot.blocking_info.end}`;
        } else {
            slotElement.classList.add('blocked-by-other');
            slotElement.title = `Blocked: ${slot.blocking_info.start} - ${slot.blocking_info.end}`;
        }
    }
    
    timeline.appendChild(slotElement);
});
```

### Statistics Dashboard
```javascript
const stats = response.metadata;
console.log(`${stats.available_count} of ${stats.total_slots_checked} slots available`);
console.log(`Service duration: ${stats.duration_minutes}min + ${stats.grace_minutes}min grace`);
```

### Rescheduling UI
```javascript
// Show only user's existing appointments for rescheduling
const userAppointments = response.unavailable_slots.filter(slot => 
    slot.owned_by_current_user
);

userAppointments.forEach(slot => {
    addRescheduleOption(slot.blocking_info.appointment_id, slot.time);
});
```

## Server-Side Features

### Service-Driven Durations
- Duration calculated from `services.duration_max_minutes` (preferred) or `duration_minutes`
- No client-side duration spoofing allowed
- Conservative scheduling using max duration + grace period

### Admin-Managed Grace Periods
- Grace periods read from `writable/grace_periods.json`
- Configurable per service type
- Default 15-20 minutes buffer between appointments

### Comprehensive Conflict Detection
- Existing appointments with full duration + grace
- Dentist availability blocks
- Branch operating hours
- Service-specific time requirements

## Migration Notes

### For Existing Frontend Code
- `response.slots` continues to work exactly as before
- No breaking changes to existing implementations
- New features are purely additive

### For New Frontend Features
- Use `response.available_slots` and `response.unavailable_slots` for separated data
- Leverage `owned_by_current_user` flags for personalized UI
- Use `response.metadata` for statistics and configuration
- Access `blocking_info` for detailed conflict reasons

### Performance Considerations
- Response includes more data but with better structure
- Frontend can cache slot data more effectively
- Reduced need for additional API calls to get appointment details