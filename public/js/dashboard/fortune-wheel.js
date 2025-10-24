/**
 * Fortune Wheel Modal System
 * Complete wheel functionality with modal, spinning, and prize handling
 */

class FortuneWheel {
    constructor() {
        this.wheel = null;
        this.wheelSpinning = false;
        this.spinsRemaining = 3;
        this.currentResult = null;
        this.scriptsLoaded = false;
        this.tickSoundEnabled = false;
        this.winSoundEnabled = false;
        this.tickInterval = null;
        this.lastPinNumber = 0;
    }

    // Load required scripts for fortune wheel
    loadWheelScripts() {
        if (window.Winwheel && window.TweenMax) {
                return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            // Load TweenMax first
            const tweenScript = document.createElement('script');
            tweenScript.src = DashboardConfig.baseUrl + 'js/TweenMax.min.js';
            tweenScript.onload = () => {
                // Then load Winwheel
                const winwheelScript = document.createElement('script');
                winwheelScript.src = DashboardConfig.baseUrl + 'js/Winwheel.min.js';
                winwheelScript.onload = () => {
                    resolve();
                };
                winwheelScript.onerror = reject;
                document.head.appendChild(winwheelScript);
            };
            tweenScript.onerror = reject;
            document.head.appendChild(tweenScript);
        });
    }

    // Add this to your Fortune Wheel class - Aggressive backdrop prevention
    startBackdropMonitor() {
        // Clear any existing monitor
        if (this.backdropMonitor) {
            clearInterval(this.backdropMonitor);
        }
        
        // Monitor for backdrops every 100ms and remove them
        this.backdropMonitor = setInterval(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 0) {
                backdrops.forEach((backdrop, index) => {
                    backdrop.remove();
                });
                
                // Also reset body state
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }, 100);
        
        // Stop monitoring after 10 seconds
        setTimeout(() => {
            if (this.backdropMonitor) {
                clearInterval(this.backdropMonitor);
                this.backdropMonitor = null;
            }
        }, 10000);
    }

    stopBackdropMonitor() {
        if (this.backdropMonitor) {
            clearInterval(this.backdropMonitor);
            this.backdropMonitor = null;
        }
    }

    fetchDataAndShow() {
        // Start monitoring for backdrops immediately
        this.startBackdropMonitor();
        
        fetch(DashboardConfig.endpoints.wheelData, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.spinsRemaining = data.spins_remaining || 0;
                this.wheelItems = data.wheel_items || [];
                this.tickSoundEnabled = data.spin_sound?.enabled || false;
                this.winSoundEnabled = data.win_sound?.enabled || false;
                
                // Force cleanup before creating modal
                this.forceCleanupBackdrops();
                
                // Create modal without backdrop
                this.createModal();
                
                setTimeout(() => {
                    const wheelModalElement = document.getElementById('wheelModal');
                    if (!wheelModalElement) {
                        return;
                    }
                    
                    // Create modal with NO BACKDROP to prevent the issue
                    const wheelModal = new bootstrap.Modal(wheelModalElement, {
                        backdrop: false,  // DISABLE BACKDROP COMPLETELY
                        keyboard: true,
                        focus: true
                    });
                    
                    // Add event listeners
                    wheelModalElement.addEventListener('shown.bs.modal', () => {
                        // Continue backdrop monitoring while modal is open
                        setTimeout(() => {
                            this.initialize();
                        }, DashboardConfig.settings.wheelRefreshDelay);
                    });
                    
                    wheelModalElement.addEventListener('hidden.bs.modal', () => {
                        this.stopBackdropMonitor();
                        this.cleanupWheel();
                        this.forceCleanupBackdrops();
                    });
                    
                    wheelModal.show();
                    
                }, 200);
                
            } else {
                this.stopBackdropMonitor();
                DashboardUtils.showToast(data.message || 'Failed to load wheel data', 'error');
            }
        })
        .catch(error => {
            this.stopBackdropMonitor();
            DashboardUtils.showToast('Failed to load wheel data', 'error');
        });
    }

    // Force cleanup method
    forceCleanupBackdrops() {
        // Remove all backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach((backdrop, index) => {
            backdrop.remove();
        });
        
        // Reset body completely
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.style.marginRight = '';
        
        // Remove any other modal-related classes
        document.documentElement.classList.remove('modal-open');
    }
    
    openModal() {
        // Force cleanup any existing backdrops first
        this.forceCleanupBackdrops();
        
        // Check if libraries are already loaded (from CDN)
        if (window.Winwheel && window.TweenMax) {
            this.scriptsLoaded = true;
            this.fetchDataAndShow();
            return;
        }
        
        // Fallback to dynamic loading if not loaded
        if (!this.scriptsLoaded) {
            DashboardUtils.showToast('Loading wheel game...', 'info', null, true);
            
            this.loadWheelScripts()
                .then(() => {
                    this.scriptsLoaded = true;
                    DashboardUtils.dismissAllToasts();
                    
                    setTimeout(() => {
                        this.fetchDataAndShow();
                    }, 100);
                })
                .catch((error) => {
                    DashboardUtils.dismissAllToasts();
                    DashboardUtils.showToast('Failed to load game. Please try again.', 'error', 5000, true);
                });
        } else {
            this.fetchDataAndShow();
        }
    }

    // Enhanced modal cleanup with better timing
    cleanupAllModals() {
        return new Promise((resolve) => {
            // 1. Hide and dispose all existing modals
            const existingModals = document.querySelectorAll('#wheelModal');
            const disposalPromises = [];
            
            existingModals.forEach(modal => {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) {
                    try {
                        const disposalPromise = new Promise((resolveDisposal) => {
                            // Listen for modal to be fully hidden
                            modal.addEventListener('hidden.bs.modal', () => {
                                try {
                                    instance.dispose();
                                } catch (e) {
                                    // Disposal error
                                }
                                resolveDisposal();
                            }, { once: true });
                            
                            // Hide the modal
                            instance.hide();
                        });
                        
                        disposalPromises.push(disposalPromise);
                    } catch (e) {
                        // Modal hide error
                    }
                }
                modal.remove();
            });
            
            // 2. Wait for all disposals to complete, then clean up
            Promise.all(disposalPromises).finally(() => {
                // Force cleanup after disposals
                setTimeout(() => {
                    // Remove all modal backdrops
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        backdrop.remove();
                    });
                    
                    // Reset body styles
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // Remove any leftover modal styles
                    const modalOpenElements = document.querySelectorAll('.modal-open');
                    modalOpenElements.forEach(element => {
                        element.classList.remove('modal-open');
                    });
                    
                    resolve();
                }, 100); // Small delay to ensure Bootstrap cleanup is done
            });
            
            // If no modals to dispose, resolve immediately
            if (disposalPromises.length === 0) {
                setTimeout(() => {
                    // Still clean up any leftover backdrops
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    resolve();
                }, 50);
            }
        });
    }

    // NEW METHOD: Cleanup wheel instance
    cleanupWheel() {
        if (this.wheel) {
            try {
                // If Winwheel has a destroy method, use it
                if (typeof this.wheel.destroy === 'function') {
                    this.wheel.destroy();
                }
                this.wheel = null;
            } catch (e) {
                // Wheel cleanup error
            }
        }
    }

    // Create wheel modal HTML
    createModal() {
        const existingModal = document.getElementById('wheelModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modalHTML = this.getModalHTML();
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Get modal HTML template
    getModalHTML() {
        return `
            <div class="modal fade" id="wheelModal" tabindex="-1" aria-labelledby="wheelModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content" style="
                        background: #000;
                        border: none;
                        border-radius: 0;
                        max-width: 414px;
                        margin: 0 auto;
                    ">
                        <div class="modal-header" style="
                            background: #000;
                            border-bottom: none;
                            padding: 10px 15px;
                            position: relative;
                        ">
                            <button type="button" class="btn-close btn-close-white position-absolute" 
                                    style="top: 10px; right: 15px; z-index: 1000;" 
                                    data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body p-0" style="background: #000;">
                            <!-- Main Content -->
                            <div class="container-fluid" style="padding: 0;">
                                <!-- Wheel Header -->
                                <article class="text-center my-3 pb-3" style="padding: 0 20px;">
                                    <p class="m-0 fw-bold text-warning blinking">
                                        You have <b id="modalSpinsCount">${this.spinsRemaining}</b> Spin Tokens Left!
                                    </p>
                                    <label style="color: #fff;">
                                        Use your tokens to spin the wheel!
                                    </label>
                                </article>
                                
                                <!-- Fortune Wheel Section -->
                                <section class="wrap-fortuneWheel pb-5" style="
                                    background: url('${DashboardConfig.baseUrl}img/fortune_wheel/bg_spin.png') no-repeat center center;
                                    background-size: 100%;
                                    padding: 0 20px;
                                ">
                                    <!-- Text Frame -->
                                    <section class="wrap-text-frame text-center" style="
                                        font-size: .8rem;
                                        padding: 1rem;
                                        margin: 0 2rem;
                                        background: url('${DashboardConfig.baseUrl}img/fortune_wheel/text_frame.png') no-repeat center center;
                                        background-size: 100% 100%;
                                    ">
                                        <p class="m-0 glow" style="color: #fff;">
                                            Free <span id="modalFreeSpinsText">${this.spinsRemaining}</span> Spins!
                                        </p>
                                        <p class="m-0" style="color: #fff;">
                                            Try your luck now and win up to 120% BONUS!
                                        </p>
                                    </section>
                                
                                    <!-- Wheel Container -->
                                    <figure class="d-block m-0 pt-1 p-4 innerWheel position-relative">
                                        <!-- Arrow -->
                                        <div class="wheel-arrow" style="
                                            width: 50px;
                                            height: 50px;
                                            margin: 0 auto -40px auto;
                                            background: url('${DashboardConfig.baseUrl}img/fortune_wheel/arrow.png') no-repeat top center;
                                            background-size: contain;
                                            z-index: 20;
                                            position: relative;
                                        "></div>
                                        
                                        <!-- Canvas with frame background -->
                                        <canvas id="fortuneWheelModal" 
                                                width="460" 
                                                height="460"
                                                data-responsiveMinWidth="180" 
                                                data-responsiveScaleHeight="true" 
                                                data-responsiveMargin="50"
                                                style="
                                                    padding: auto;
                                                    max-width: 100%;
                                                    width: 100%;
                                                    height: 100%;
                                                    display: inline-block;
                                                    background: url('${DashboardConfig.baseUrl}img/fortune_wheel/bg_wheel_frame.png') no-repeat top center;
                                                    background-size: 100%;
                                                    position: relative;
                                                    z-index: 0;
                                                    overflow: hidden;
                                                ">
                                            <p class="text-white text-center">
                                                Sorry, your browser doesn't support canvas. Please try another.
                                            </p>
                                        </canvas>
                                    </figure>
                                    
                                    <!-- Spin Button -->
                                    <div class="text-center position-relative">
                                        <button class="btn btn-warning bg-gradient" 
                                                id="modalSpinButton" 
                                                onclick="fortuneWheelInstance.startSpin()"
                                                ${this.spinsRemaining <= 0 ? 'disabled' : ''}>
                                            <span id="modalSpinButtonText">
                                                ${this.spinsRemaining > 0 ? 'Spin the Wheel' : 'Out of Spins'}
                                            </span>
                                        </button>
                                    </div>
                                </section>
                                
                                <!-- Result Display -->
                                <div id="modalWheelResult" style="
                                    display: none; 
                                    margin: 20px; 
                                    padding: 20px; 
                                    background: rgba(255, 215, 0, 0.1); 
                                    border-radius: 10px; 
                                    border: 1px solid #ffd700;
                                ">
                                    <h4 class="text-warning">🎉 Congratulations!</h4>
                                    <p id="modalResultText" class="text-white"></p>
                                    <button class="btn btn-warning" onclick="fortuneWheelInstance.claimPrize()" id="modalClaimBtn">
                                        Claim Prize
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    addModalStyles() {
        if (document.getElementById('modal-wheel-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'modal-wheel-styles';
        style.textContent = `
            /* Modal Wheel Specific Styles */
            #wheelModal .modal-dialog {
                max-width: 414px;
                margin: 0 auto;
            }
            
            #wheelModal .modal-content {
                background: #000 !important;
                border: none !important;
                border-radius: 0 !important;
            }
            
            /* Glow Animation */
            .glow {
                color: #fff;
                animation: glow 1s ease-in-out infinite alternate;
            }
            
            @keyframes glow {
                from {
                    text-shadow: 0 0 1px #fff, 0 0 5px #fff, 0 0 10px #e60073, 0 0 20px #e60073, 0 0 30px #e60073;
                }
                to {
                    text-shadow: 0 0 1px #fff, 0 0 10px #ff4da6, 0 0 20px #ff4da6, 0 0 30px #ff4da6, 0 0 50px #ff4da6;
                }
            }
            
            /* Blinking Animation */
            .blinking {
                animation: blinker 1s linear infinite;
            }
            
            @keyframes blinker {
                50% {
                    opacity: 0;
                }
            }
            
            /* Mobile Responsive */
            @media (max-width: 414px) {
                #wheelModal .modal-dialog {
                    margin: 0;
                    max-width: 100%;
                    width: 100%;
                    height: 100vh;
                }
                
                #wheelModal .modal-content {
                    height: 100vh;
                    border-radius: 0;
                }
                
                .wrap-text-frame {
                    margin: 0 1rem;
                    font-size: 0.7rem;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize wheel after modal is shown
    initialize() {
        const canvas = document.getElementById('fortuneWheelModal');
        if (!canvas) {
            return;
        }
        
        if (!this.wheelItems || this.wheelItems.length === 0) {
            DashboardUtils.showToast('Wheel data not available', 'error');
            return;
        }

        // Sort items by order first to ensure correct positioning
        const sortedItems = [...this.wheelItems].sort((a, b) => (a.order || 0) - (b.order || 0));
        
        // Helper function to format text for multi-line display
        const formatTextForWheel = (text, maxCharsPerLine = 15) => {
            if (text.length <= maxCharsPerLine) {
                return text;
            }
            
            // Split by common separators and try to break at logical points
            const words = text.split(/(\s+)/);
            const lines = [];
            let currentLine = '';
            
            for (let i = 0; i < words.length; i++) {
                const word = words[i];
                if ((currentLine + word).length <= maxCharsPerLine) {
                    currentLine += word;
                } else {
                    if (currentLine.trim()) {
                        lines.push(currentLine.trim());
                    }
                    currentLine = word;
                }
            }
            
            if (currentLine.trim()) {
                lines.push(currentLine.trim());
            }
            
            return lines.join('\n');
        };

        // Create segments array for the wheel
        const segments = [];
        sortedItems.forEach((item, index) => {
            const useRedBackground = index % 2 === 0;
            const textColor = useRedBackground ? "#f8b500" : "#000";
            
            segments.push({
                'text': formatTextForWheel(item.item_name),
                'textFillStyle': textColor,
                'image': `${DashboardConfig.baseUrl}img/fortune_wheel/${useRedBackground ? 'red.png' : 'brown.png'}`,
                'itemData': {
                    name: item.item_name,
                    prize: item.item_prize,
                    type: item.item_types,
                    winningRate: item.winning_rate,
                    order: item.order,
                    id: item.item_id
                }
            });
        });

        try {
            // Create the wheel with multi-line text support
            this.wheel = new Winwheel({
                'canvasId': 'fortuneWheelModal',
                'numSegments': segments.length,
                'outerRadius': 200,  // ORIGINAL value
                'responsive': true,
                'drawMode': 'segmentImage',
                'drawText': true,
                'textFontSize': 14,  // Reduced for multi-line support
                'textFontWeight': 'bold',
                'textOrientation': 'horizontal',
                'textAlignment': 'center',
                'textDirection': 'reversed',
                'textMargin': 12,  // Reduced margin for better spacing
                'textFontFamily': 'Arial, sans-serif',
                'segments': segments,
                'pins': {
                    'number': segments.length * 2,
                    'outerRadius': 5,  // ORIGINAL value
                    'responsive': true,
                    'margin': 5,  // ORIGINAL value
                    'fillStyle': '#f8b500',
                    'strokeStyle': '#f8b500'
                },
                'animation': {
                    'type': 'spinToStop',
                    'duration': 8,
                    'spins': 12,
                    'callbackFinished': (indicatedSegment) => this.alertPrize(indicatedSegment),
                    'callbackBefore': () => this.animationBefore(),
                    'callbackAfter': () => this.animationAfter(),
                    'easing': 'Power3.easeOut'
                }
            });

            this.updateSpinsCounter();
            this.addModalStyles();
            
        } catch (error) {
            DashboardUtils.showToast('Failed to initialize wheel', 'error');
        }
    }

    // Start spinning the wheel
    startSpin() {
        if (this.wheelSpinning === true) {
            return;
        }
        
        if (this.spinsRemaining <= 0) {
            DashboardUtils.showToast('No spin tokens remaining! Contact customer service to get more.', 'warning');
            return;
        }

        if (!this.wheel) {
            return;
        }
        
        this.wheelSpinning = true;
        this.lastPinNumber = 0;
        
        // Update button state
        const spinButton = document.getElementById('modalSpinButton');
        const spinButtonText = document.getElementById('modalSpinButtonText');
        
        spinButton.disabled = true;
        if (spinButtonText) {
            spinButtonText.textContent = 'SPINNING...';
        }

        // Determine winner and start animation
        const winnerResult = this.determineWinner();
        const winnerIndex = winnerResult.winnerIndex;
        const winnerItem = winnerResult.winnerItem;
        
        // Calculate target angle
        const segmentAngle = 360 / this.wheel.numSegments;
        const targetAngle = (winnerIndex * segmentAngle) + (segmentAngle / 2);
        const additionalSpins = Math.floor(Math.random() * 5) + 6;
        const finalRotation = (360 * additionalSpins) + targetAngle;
        
        // Set wheel to stop at predetermined segment
        this.wheel.animation.stopAngle = finalRotation;
        this.wheel.winnerData = winnerItem;
        
        // Start animation
        this.wheel.startAnimation();
        
        // Update spins
        this.spinsRemaining--;
        this.updateSpinsCounter();
        
        // Record spin
        this.recordSpinResult(winnerItem);
    }

    // Determine winner based on winning rates
    determineWinner() {
        // Filter items with winning rate > 0
        const winnableItems = this.wheelItems
            .map((item, index) => ({ item, index }))
            .filter(({ item }) => parseFloat(item.winning_rate || 0) > 0);
        
        // If no winnable items, return first item
        if (winnableItems.length === 0) {
            const firstItem = this.wheelItems[0];
            return {
                winnerIndex: 0,
                winnerItem: {
                    name: firstItem.item_name,
                    prize: firstItem.item_prize,
                    type: firstItem.item_types,
                    winningRate: firstItem.winning_rate,
                    order: firstItem.order,
                    id: firstItem.item_id
                }
            };
        }
        
        // Calculate total winning rate from winnable items only
        const totalRate = winnableItems.reduce((sum, { item }) => 
            sum + parseFloat(item.winning_rate || 0), 0
        );
        
        // Generate random number between 0 and total rate
        const rand = Math.random() * totalRate;
        let cumulativeRate = 0;
        
        // Find winner based on weighted probability
        for (const { item, index } of winnableItems) {
            const itemRate = parseFloat(item.winning_rate || 0);
            cumulativeRate += itemRate;
            
            if (rand <= cumulativeRate) {
                return {
                    winnerIndex: index,
                    winnerItem: {
                        name: item.item_name,
                        prize: item.item_prize,
                        type: item.item_types,
                        winningRate: item.winning_rate,
                        order: item.order,
                        id: item.item_id
                    }
                };
            }
        }
        
        // Fallback to first winnable item
        const { item, index } = winnableItems[0];
        return {
            winnerIndex: index,
            winnerItem: {
                name: item.item_name,
                prize: item.item_prize,
                type: item.item_types,
                winningRate: item.winning_rate,
                order: item.order,
                id: item.item_id
            }
        };
    }

    // Handle animation callbacks
    animationBefore() {
        if (this.tickSoundEnabled && this.wheel) {
            const currentPinNumber = this.wheel.getCurrentPinNumber();
            if (currentPinNumber !== this.lastPinNumber) {
                this.playTickSound();
                this.lastPinNumber = currentPinNumber;
            }
        }
    }

    animationAfter() {
        // Called after each frame update
    }

    // Handle prize alert when wheel stops
    alertPrize(indicatedSegment) {
        const winner = this.wheel.winnerData || indicatedSegment.itemData;
        
        if (!winner) {
            this.resetSpinButton();
            return;
        }

        this.currentResult = winner;

        // Play win sound for actual prizes
        if (winner.name !== 'Try Again' && this.winSoundEnabled) {
            this.playWinSound();
        }

        // Handle different types of wins
        if (winner.name === 'Try Again') {
            this.showTryAgain(winner);
        } else if (winner.name.includes('120%') || winner.name.includes('BONUS') || winner.type === 'product') {
            this.showBonusWin(winner);
        } else {
            this.showRegularWin(winner);
        }

        // Reset button after delay
        setTimeout(() => {
            this.resetSpinButton();
        }, 2000);
    }

    // Show different result types
    showTryAgain(winner) {
        const resultDiv = document.getElementById('modalWheelResult');
        const resultText = document.getElementById('modalResultText');
        const claimBtn = document.getElementById('modalClaimBtn');
        
        resultText.innerHTML = '😔 Try Again!<br>Better luck next time!';
        claimBtn.textContent = this.spinsRemaining > 0 ? 'Continue Playing' : 'Close';
        claimBtn.className = 'btn btn-secondary';
        claimBtn.onclick = () => {
            resultDiv.style.display = 'none';
            if (this.spinsRemaining <= 0) {
                bootstrap.Modal.getInstance(document.getElementById('wheelModal')).hide();
            }
        };
        
        resultDiv.style.display = 'block';
    }

    showRegularWin(winner) {
        const resultDiv = document.getElementById('modalWheelResult');
        const resultText = document.getElementById('modalResultText');
        const claimBtn = document.getElementById('modalClaimBtn');
        
        resultText.innerHTML = `🎉 You Won!<br><strong>${winner.name}</strong>${winner.prize > 0 ? '<br>+' + winner.prize + ' points' : ''}`;
        claimBtn.textContent = 'Collect Prize';
        claimBtn.className = 'btn btn-success';
        claimBtn.onclick = () => {
            resultDiv.style.display = 'none';
            DashboardUtils.showToast(`Collected: ${winner.name}`, 'success');
        };
        
        resultDiv.style.display = 'block';
    }

    showBonusWin(winner) {
        const resultDiv = document.getElementById('modalWheelResult');
        const resultText = document.getElementById('modalResultText');
        const claimBtn = document.getElementById('modalClaimBtn');
        
        resultText.innerHTML = `🎉 CONGRATULATIONS!<br><strong>${winner.name}</strong><br>Claim your bonus now!`;
        claimBtn.textContent = 'Claim Reward';
        claimBtn.className = 'btn btn-warning';
        claimBtn.onclick = () => this.claimPrize();
        
        resultDiv.style.display = 'block';
    }

    // Claim prize functionality
    claimPrize() {
        if (!this.currentResult) return;
        
        const winnerData = {
            name: this.currentResult.name,
            prize: this.currentResult.prize || 0,
            type: this.currentResult.type || 'product'
        };
        
        const formData = new FormData();
        formData.append('winner_data', JSON.stringify(winnerData));
        formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
        
        fetch(DashboardConfig.endpoints.storeWinner, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('wheelModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                
                DashboardUtils.showToast('Prize stored! Redirecting to claim page...', 'success');
                setTimeout(() => {
                    window.location.href = DashboardConfig.endpoints.reward;
                }, DashboardConfig.settings.redirectDelay);
            } else {
                DashboardUtils.showToast('Failed to store prize. Please try again.', 'error');
            }
        })
        .catch(error => {
            DashboardUtils.showToast('Failed to store prize. Please try again.', 'error');
        });
    }

    // Reset spin button
    resetSpinButton() {
        this.wheelSpinning = false;
        const spinButton = document.getElementById('modalSpinButton');
        const spinButtonText = document.getElementById('modalSpinButtonText');
        
        if (this.spinsRemaining > 0) {
            spinButton.disabled = false;
            if (spinButtonText) {
                spinButtonText.textContent = 'Spin the Wheel';
            }
        } else {
            spinButton.disabled = true;
            if (spinButtonText) {
                spinButtonText.textContent = 'Out of Spins';
            }
        }
    }

    // Update spins counter
    updateSpinsCounter() {
        const spinsCounter = document.getElementById('modalSpinsCount');
        if (spinsCounter) {
            spinsCounter.textContent = this.spinsRemaining;
        }
    }

    // Record spin result
    recordSpinResult(winner) {
        const formData = new FormData();
        formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
        formData.append('predetermined_outcome', JSON.stringify({
            item_name: winner.name,
            item_prize: winner.prize,
            item_types: winner.type,
            index: winner.order
        }));

        fetch(DashboardConfig.endpoints.spin, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Spin recorded
        })
        .catch(error => {
            // Error recording spin
        });
    }

    // Sound functions
    playTickSound() {
        if (!document.getElementById('modalTickAudio')) {
            const audio = document.createElement('audio');
            audio.id = 'modalTickAudio';
            audio.preload = 'auto';
            audio.volume = 0.3;
            document.body.appendChild(audio);
        }
        
        const tickAudio = document.getElementById('modalTickAudio');
        if (tickAudio && this.tickSoundEnabled) {
            tickAudio.currentTime = 0;
            tickAudio.play().catch(e => {
                // Tick sound play failed
            });
        }
    }

    playWinSound() {
        if (!document.getElementById('modalWinAudio')) {
            const audio = document.createElement('audio');
            audio.id = 'modalWinAudio';
            audio.preload = 'auto';
            audio.volume = 0.8;
            document.body.appendChild(audio);
        }
        
        const winAudio = document.getElementById('modalWinAudio');
        if (winAudio && this.winSoundEnabled) {
            winAudio.currentTime = 0;
            winAudio.play().catch(e => {
                // Win sound play failed
            });
        }
    }
}