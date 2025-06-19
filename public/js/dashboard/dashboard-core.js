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

    // Upload background image functionality
    static uploadBackgroundImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                DashboardUtils.showToast('File size too large. Maximum size is 5MB', 'error');
                input.value = '';
                return;
            }
            
            // Show loading
            const uploadLoading = document.getElementById('uploadLoading');
            if (uploadLoading) {
                uploadLoading.classList.add('active');
            }
            
            const formData = new FormData();
            formData.append('background_image', file);
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
                if (uploadLoading) {
                    uploadLoading.classList.remove('active');
                }
                
                if (data.success) {
                    // Update background immediately
                    const header = document.getElementById('profileHeader');
                    if (header) {
                        header.style.backgroundImage = `url(${data.image_url})`;
                        
                        // Add overlay if not exists
                        if (!header.querySelector('.profile-header-overlay')) {
                            const overlay = document.createElement('div');
                            overlay.className = 'profile-header-overlay';
                            header.insertBefore(overlay, header.firstChild);
                        }
                    }
                    
                    DashboardUtils.showToast('Background image updated successfully!', 'success');
                    
                    // Reload to show remove button
                    setTimeout(() => location.reload(), 1500);
                } else {
                    DashboardUtils.showToast(data.message || 'Upload failed', 'error');
                }
            })
            .catch(error => {
                if (uploadLoading) {
                    uploadLoading.classList.remove('active');
                }
                console.error('Upload error:', error);
                DashboardUtils.showToast('Failed to upload image', 'error');
            });
            
            // Clear input
            input.value = '';
        }
    }

    // Remove background image functionality
    static removeBackgroundImage() {
        DashboardUtils.confirmAction('Are you sure you want to remove your background image?', () => {
            const formData = new FormData();
            formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
            
            fetch(DashboardConfig.endpoints.removeBackground || '/customer/remove-background', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    DashboardUtils.showToast('Background image removed!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    DashboardUtils.showToast(data.message || 'Failed to remove', 'error');
                }
            })
            .catch(error => {
                console.error('Remove error:', error);
                DashboardUtils.showToast('Failed to remove image', 'error');
            });
        });
    }

    // Legacy change background functionality (for color backgrounds)
    // You can remove this if you're completely replacing colors with images
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
        // Redirect to wheel functionality
        if (typeof openWheelModal === 'function') {
            openWheelModal();
        }
    }
}