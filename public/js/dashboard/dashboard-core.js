/**
 * Core Dashboard Functions
 * Basic dashboard functionality like check-in, background changes, navigation
 */

class DashboardCore {
    
    // Check-in functionality
    static performCheckin() {
        const formData = new FormData();
        formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
        
        fetch(DashboardConfig.endpoints.checkin, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const checkinBtn = document.querySelector('.btn-checkin');
                if (checkinBtn) {
                    checkinBtn.disabled = true;
                    checkinBtn.textContent = 'Already Checked In';
                }
                
                // Show success message
                DashboardUtils.showToast('Check-in successful! +' + (data.points || 10) + ' points', 'success');
                
                // Refresh page after delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                DashboardUtils.showToast(data.message || 'Check-in failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            DashboardUtils.showToast('Check-in failed. Please try again.', 'error');
        });
    }

    // Navigation functions
    static redirectToDeposit() {
        window.open(DashboardConfig.endpoints.bonus, '_blank');
    }

    static openSettings() {
        // Redirect to wheel functionality
        if (typeof openWheelModal === 'function') {
            openWheelModal();
        }
    }
}