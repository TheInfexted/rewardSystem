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

    // Change background functionality
    static changeBackground(bgType) {
        console.log('Changing background to:', bgType);
        
        // Update UI immediately for better UX
        const profileHeader = document.querySelector('.profile-header');
        if (profileHeader) {
            // Remove ALL background classes first
            profileHeader.classList.remove('default-bg', 'blue-bg', 'green-bg', 'purple-bg', 'gold-bg');
            // Add new background class
            profileHeader.classList.add(`${bgType}-bg`);
            
            // Update active button immediately
            document.querySelectorAll('.bg-option').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const selectedBtn = document.querySelector(`[data-bg="${bgType}"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('active');
            }
        }
        
        // Then save to database
        const formData = new FormData();
        formData.append('background', bgType);
        formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
        
        fetch(DashboardConfig.endpoints.updateBackground, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                DashboardUtils.showToast('Background updated successfully!', 'success');
            } else {
                DashboardUtils.showToast('Background changed but not saved: ' + (data.message || 'Unknown error'), 'warning');
            }
        })
        .catch(error => {
            console.error('Background save error:', error);
            DashboardUtils.showToast('Background changed but not saved to database', 'warning');
        });
    }

    // Navigation functions
    static redirectToDeposit() {
        window.open(DashboardConfig.endpoints.bonus, '_blank');
    }

    static openSettings() {
        DashboardUtils.showToast('Settings feature coming soon!', 'info');
    }
}