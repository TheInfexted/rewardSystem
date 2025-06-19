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
                    <div class="modal-content" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border: 2px solid #ffd700; border-radius: 15px;">
                        <div class="modal-header" style="border-bottom: 1px solid #ffd700; color: #fff;">
                            <h5 class="modal-title" id="wheelModalLabel">
                                <i class="bi bi-pie-chart-fill text-warning me-2"></i>Fortune Wheel
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center" style="color: #fff; padding: 20px; background: url('${DashboardConfig.baseUrl}img/fortune_wheel/bg_spin.png') no-repeat center center; background-size: cover;">
                            <!-- Spin Info -->
                            <div class="wrap-text-frame text-center mb-3" style="font-size: .8rem; padding: 1rem; margin: 0 2rem; background: url('${DashboardConfig.baseUrl}img/fortune_wheel/text_frame.png') no-repeat center center; background-size: 100% 100%;">
                                <p class="m-0 glow" style="color: #fff; animation: glow 1s ease-in-out infinite alternate;">Free <span id="modalSpinsCount">${this.spinsRemaining}</span> Spins!</p>
                                <p class="m-0" style="color: #fff;">Try your luck now and win up to 120% BONUS!</p>
                            </div>
                            
                            <!-- Wheel Container -->
                            <div class="wheel-container" style="display: flex; justify-content: center; align-items: center; width: 100%; padding: 1rem 0; position: relative;">
                                <div class="innerWheel text-center" style="position: relative; width: 100%; max-width: 360px;">
                                    <!-- Arrow -->
                                    <div style="width: 50px; height: 50px; margin: 0 auto -35px auto; background: url('${DashboardConfig.baseUrl}img/fortune_wheel/arrow.png') no-repeat center center; background-size: contain; z-index: 20; position: relative;"></div>
                                    
                                    <div class="wheel-wrapper" style="width: 100%; max-width: 320px; aspect-ratio: 1 / 1; margin: 0 auto; position: relative; background: url('${DashboardConfig.baseUrl}img/fortune_wheel/bg_wheel_frame.png') no-repeat center center; background-size: contain;">
                                        <canvas id="fortuneWheelModal" width="320" height="320" style="width: 100%; height: 100%; border-radius: 50%; background-color: transparent; display: block;">
                                            <p>Your browser does not support canvas.</p>
                                        </canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Spin Button -->
                            <div class="text-center position-relative mt-3">
                                <button class="btn btn-warning bg-gradient" id="modalSpinButton" onclick="fortuneWheelInstance.startSpin()" ${this.spinsRemaining <= 0 ? 'disabled' : ''}>
                                    <span id="modalSpinButtonText">${this.spinsRemaining > 0 ? 'Spin the Wheel' : 'Out of Spins'}</span>
                                </button>
                            </div>
                            
                            <!-- Result Display -->
                            <div id="modalWheelResult" style="display: none; margin-top: 20px; padding: 20px; background: rgba(255, 215, 0, 0.1); border-radius: 10px; border: 1px solid #ffd700;">
                                <h4 class="text-warning">🎉 Congratulations!</h4>
                                <p id="modalResultText" class="text-white"></p>
                                <button class="btn btn-warning" onclick="fortuneWheelInstance.claimPrize()" id="modalClaimBtn">Claim Prize</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
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

        // Create segments array for the wheel
        const segments = [];
        this.wheelItems.forEach((item, index) => {
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

        // Create the wheel
        this.wheel = new Winwheel({
            'canvasId': 'fortuneWheelModal',
            'numSegments': segments.length,
            'outerRadius': 150,
            'responsive': true,
            'drawMode': 'segmentImage',
            'drawText': true,
            'textFontSize': 14,
            'textFontWeight': 'bold',
            'textOrientation': 'horizontal',
            'textAlignment': 'center',
            'textDirection': 'reversed',
            'textMargin': 15,
            'textFontFamily': 'Arial, sans-serif',
            'segments': segments,
            'pins': {
                'number': segments.length * 2,
                'outerRadius': 4,
                'responsive': true,
                'margin': 5,
                'fillStyle': '#f8b500',
                'strokeStyle': '#f8b500'
            },
            'animation': {
                'type': 'spinToStop',
                'duration': 6,
                'spins': 8,
                'callbackFinished': (indicatedSegment) => this.alertPrize(indicatedSegment),
                'callbackBefore': () => this.animationBefore(),
                'callbackAfter': () => this.animationAfter(),
                'easing': 'Power3.easeOut'
            }
        });

        console.log('Modal wheel created successfully:', this.wheel);
        this.updateSpinsCounter();
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
        const rand = Math.random() * 100;
        let cumulativeRate = 0;
        
        for (let i = 0; i < this.wheelItems.length; i++) {
            const item = this.wheelItems[i];
            cumulativeRate += parseFloat(item.winning_rate || 0);
            if (rand <= cumulativeRate) {
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
        
        // Fallback to random
        const randomIndex = Math.floor(Math.random() * this.wheelItems.length);
        const randomItem = this.wheelItems[randomIndex];
        return {
            winnerIndex: randomIndex,
            winnerItem: {
                name: randomItem.item_name,
                prize: randomItem.item_prize,
                type: randomItem.item_types,
                winningRate: randomItem.winning_rate,
                order: randomItem.order,
                id: randomItem.item_id
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