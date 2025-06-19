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
            console.log('Wheel scripts already loaded');
            return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            // Load TweenMax first
            const tweenScript = document.createElement('script');
            tweenScript.src = DashboardConfig.baseUrl + 'js/TweenMax.min.js';
            tweenScript.onload = () => {
                console.log('TweenMax loaded');
                
                // Then load Winwheel
                const winwheelScript = document.createElement('script');
                winwheelScript.src = DashboardConfig.baseUrl + 'js/Winwheel.min.js';
                winwheelScript.onload = () => {
                    console.log('Winwheel loaded');
                    resolve();
                };
                winwheelScript.onerror = reject;
                document.head.appendChild(winwheelScript);
            };
            tweenScript.onerror = reject;
            document.head.appendChild(tweenScript);
        });
    }

    // Main entry point for opening wheel modal
    openModal() {
        if (!this.scriptsLoaded) {
            DashboardUtils.showToast('Loading wheel game...', 'info');
            
            this.loadWheelScripts()
                .then(() => {
                    this.scriptsLoaded = true;
                    console.log('All wheel scripts loaded successfully');
                    this.fetchDataAndShow();
                })
                .catch((error) => {
                    console.error('Failed to load wheel scripts:', error);
                    DashboardUtils.showToast('Failed to load game. Please try again.', 'error');
                });
        } else {
            this.fetchDataAndShow();
        }
    }

    // Fetch wheel data from server
    fetchDataAndShow() {
        fetch(DashboardConfig.endpoints.wheelData, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.spinsRemaining = data.spins_remaining || 0;
                this.wheelItems = data.wheel_items || [];
                this.tickSoundEnabled = data.spin_sound?.enabled || false;
                this.winSoundEnabled = data.win_sound?.enabled || false;
                
                this.createModal();
                const wheelModal = new bootstrap.Modal(document.getElementById('wheelModal'));
                wheelModal.show();
                
                setTimeout(() => {
                    this.initialize();
                }, DashboardConfig.settings.wheelRefreshDelay);
            } else {
                DashboardUtils.showToast(data.message || 'Failed to load wheel data', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching wheel data:', error);
            DashboardUtils.showToast('Failed to load wheel data', 'error');
        });
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
                                        You have <b id="modalSpinsCount">${this.spinsRemaining}</b> Free Spins Left!
                                    </p>
                                    <label style="color: #fff;">
                                        Limited Time Event: <b class="fw-semibold text-danger">LAST CHANCE!</b>
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
                                                    padding: 1rem;
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
            console.error('Modal wheel canvas not found!');
            return;
        }
        
        if (!this.wheelItems || this.wheelItems.length === 0) {
            console.error('No wheel items available!');
            DashboardUtils.showToast('Wheel data not available', 'error');
            return;
        }
        
        console.log('Initializing modal wheel with items:', this.wheelItems);

        // Sort items by order first to ensure correct positioning
        const sortedItems = [...this.wheelItems].sort((a, b) => (a.order || 0) - (b.order || 0));
        
        // Create segments array for the wheel - SAME AS ORIGINAL
        const segments = [];
        sortedItems.forEach((item, index) => {
            const useRedBackground = index % 2 === 0;
            const textColor = useRedBackground ? "#f8b500" : "#000";
            
            segments.push({
                'text': item.item_name,
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

        console.log('Creating modal wheel with segments:', segments);

        // Create the wheel - MATCH ORIGINAL SETTINGS
        this.wheel = new Winwheel({
            'canvasId': 'fortuneWheelModal',
            'numSegments': segments.length,
            'outerRadius': 200,  // Match original
            'responsive': true,
            'drawMode': 'segmentImage',
            'drawText': true,
            'textFontSize': 16,  // Match original
            'textFontWeight': 'bold',
            'textOrientation': 'horizontal',
            'textAlignment': 'center',
            'textDirection': 'reversed',
            'textMargin': 15,
            'textFontFamily': 'Arial, sans-serif',
            'segments': segments,
            'pins': {
                'number': segments.length * 2,
                'outerRadius': 5,  // Match original
                'responsive': true,
                'margin': 5,
                'fillStyle': '#f8b500',
                'strokeStyle': '#f8b500'
            },
            'animation': {
                'type': 'spinToStop',
                'duration': 8,  // Match original
                'spins': 12,    // Match original
                'callbackFinished': (indicatedSegment) => this.alertPrize(indicatedSegment),
                'callbackBefore': () => this.animationBefore(),
                'callbackAfter': () => this.animationAfter(),
                'easing': 'Power3.easeOut'
            }
        });

        console.log('Modal wheel created successfully:', this.wheel);
        this.updateSpinsCounter();
        
        // Add modal-specific styles
        this.addModalStyles();
    }

    // Start spinning the wheel
    startSpin() {
        if (this.wheelSpinning === true) {
            console.log('Modal wheel already spinning');
            return;
        }
        
        if (this.spinsRemaining <= 0) {
            console.log('No modal spins remaining');
            DashboardUtils.showToast('No spins remaining!', 'warning');
            return;
        }

        if (!this.wheel) {
            console.error('Modal wheel not initialized!');
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
        console.log('Determining winner with wheel items:', this.wheelItems);
        
        // Calculate total winning rate to normalize if needed
        let totalRate = 0;
        this.wheelItems.forEach(item => {
            totalRate += parseFloat(item.winning_rate || 0);
        });
        
        console.log('Total winning rate:', totalRate);
        
        // Generate random number between 0 and total rate (or 100 if total is 100)
        const rand = Math.random() * (totalRate > 0 ? totalRate : 100);
        console.log('Random number generated:', rand);
        
        let cumulativeRate = 0;
        
        // Sort items by order to ensure correct positioning
        const sortedItems = [...this.wheelItems].sort((a, b) => (a.order || 0) - (b.order || 0));
        
        // Use the actual ORDER from database, not array index
        for (let i = 0; i < sortedItems.length; i++) {
            const item = sortedItems[i];
            const itemRate = parseFloat(item.winning_rate || 0);
            cumulativeRate += itemRate;
            
            console.log(`Item ${item.item_name}: rate=${itemRate}, cumulative=${cumulativeRate}, rand=${rand}`);
            
            if (rand <= cumulativeRate && itemRate > 0) {
                // Find the original index in unsorted array for wheel positioning
                const originalIndex = this.wheelItems.findIndex(originalItem => 
                    originalItem.item_id === item.item_id
                );
                
                console.log(`Winner selected: ${item.item_name} (original index: ${originalIndex})`);
                return {
                    winnerIndex: originalIndex >= 0 ? originalIndex : i,
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
        
        // Fallback to first item with winning rate > 0
        for (let i = 0; i < this.wheelItems.length; i++) {
            const item = this.wheelItems[i];
            if (parseFloat(item.winning_rate || 0) > 0) {
                console.log(`Fallback winner: ${item.item_name}`);
                return {
                    winnerIndex: i,
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
        
        // Final fallback to first item
        const firstItem = this.wheelItems[0];
        console.log(`Final fallback winner: ${firstItem.item_name}`);
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
            console.error('No winner data found!');
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
            console.error('Store winner error:', error);
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
            console.log('Spin recorded:', data);
        })
        .catch(error => {
            console.error('Error recording spin:', error);
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
            tickAudio.play().catch(e => console.log('Tick sound play failed:', e));
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
            winAudio.play().catch(e => console.log('Win sound play failed:', e));
        }
    }
}