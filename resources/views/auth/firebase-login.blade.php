<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Jesuit Community Management System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .hidden {
            display: none;
        }
    </style>
</head>

<body class="antialiased">
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
            <div class="flex justify-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Jesuit Community Management System</h1>
            </div>

            <!-- Login Options Container -->
            <div id="login-options" class="form-container">
                <div class="text-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Login</h2>
                    <p class="mt-2 text-sm text-gray-600">Select a login method below</p>
                </div>
                
                <!-- Phone Login Button -->
                <button id="phone-login-btn" class="w-full py-3 px-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow">
                    Login with Phone Number
                </button>
                
                <!-- Google Login Button -->
                <button id="google-login-btn" class="w-full py-3 px-4 bg-white hover:bg-gray-100 text-gray-800 font-semibold rounded-lg shadow border border-gray-300">
                    <svg class="inline-block w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                            <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                            <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                            <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                            <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                        </g>
                    </svg>
                    Login with Google
                </button>
            </div>

            <!-- Phone Login Form -->
            <div id="phone-login-form" class="form-container hidden">
                <div class="text-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Phone Login</h2>
                    <p class="mt-2 text-sm text-gray-600">Enter your phone number to receive an OTP</p>
                </div>

                <div id="phone-login-step-1">
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input id="phone_number" type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+91xxxxxxxxxx" required>
                        <p id="phone-error" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div id="recaptcha-container" class="mb-4"></div>

                    <div class="flex justify-between">
                        <button type="button" id="back-to-login" class="py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg">
                            Back
                        </button>
                        <button type="button" id="send-otp-btn" class="py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg flex items-center" disabled>
                            <span id="send-otp-loader" class="loader hidden"></span>
                            <span id="send-otp-text">Send OTP</span>
                        </button>
                    </div>
                </div>

                <div id="phone-login-step-2" class="hidden">
                    <div class="mb-4">
                        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                        <input id="verification_code" type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter 6-digit code" required>
                        <p id="verification-error" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" id="back-to-phone" class="py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg">
                            Back
                        </button>
                        <button type="button" id="verify-otp-btn" class="py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg flex items-center">
                            <span id="verify-otp-loader" class="loader hidden"></span>
                            <span id="verify-otp-text">Verify OTP</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Global Error Message -->
            <div id="error-message" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded hidden"></div>
        </div>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.3.1/firebase-app.js";
        import { 
            getAuth, 
            RecaptchaVerifier, 
            signInWithPhoneNumber, 
            GoogleAuthProvider, 
            signInWithPopup,
            signInWithCredential,
            PhoneAuthProvider 
        } from 'https://www.gstatic.com/firebasejs/11.3.1/firebase-auth.js';

        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyBe90_lFh9Q30PqedmIfrYAvXjW5g9Xj7c",
            authDomain: "kpap-sj.firebaseapp.com",
            projectId: "kpap-sj",
            storageBucket: "kpap-sj.appspot.com",
            messagingSenderId: "397973729528",
            appId: "1:397973729528:web:3d00143386cc02bff14b0d",
            measurementId: "G-1FB1V4BTCZ"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        auth.languageCode = 'en';
        let confirmationResult = null;

        // DOM Elements
        const loginOptions = document.getElementById('login-options');
        const phoneLoginForm = document.getElementById('phone-login-form');
        const phoneLoginStep1 = document.getElementById('phone-login-step-1');
        const phoneLoginStep2 = document.getElementById('phone-login-step-2');
        const errorMessage = document.getElementById('error-message');
        const phoneError = document.getElementById('phone-error');
        const verificationError = document.getElementById('verification-error');

        // Buttons
        const phoneLoginBtn = document.getElementById('phone-login-btn');
        const googleLoginBtn = document.getElementById('google-login-btn');
        const backToLoginBtn = document.getElementById('back-to-login');
        const backToPhoneBtn = document.getElementById('back-to-phone');
        const sendOtpBtn = document.getElementById('send-otp-btn');
        const verifyOtpBtn = document.getElementById('verify-otp-btn');

        // Loaders
        const sendOtpLoader = document.getElementById('send-otp-loader');
        const sendOtpText = document.getElementById('send-otp-text');
        const verifyOtpLoader = document.getElementById('verify-otp-loader');
        const verifyOtpText = document.getElementById('verify-otp-text');

        // Input fields
        const phoneNumberInput = document.getElementById('phone_number');
        const verificationCodeInput = document.getElementById('verification_code');

        // Show error message
        function showError(message, elementId = null) {
            if (elementId) {
                const element = document.getElementById(elementId);
                element.textContent = message;
                element.classList.remove('hidden');
            } else {
                errorMessage.textContent = message;
                errorMessage.classList.remove('hidden');
            }
        }

        // Clear error message
        function clearError(elementId = null) {
            if (elementId) {
                const element = document.getElementById(elementId);
                element.textContent = '';
                element.classList.add('hidden');
            } else {
                errorMessage.textContent = '';
                errorMessage.classList.add('hidden');
            }
        }

        // Initialize ReCaptcha verifier
        function initRecaptcha() {
            if (window.recaptchaVerifier) {
                window.recaptchaVerifier.clear();
            }
            
            window.recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
                'size': 'normal',
                'callback': (response) => {
                    sendOtpBtn.removeAttribute('disabled');
                },
                'expired-callback': () => {
                    sendOtpBtn.setAttribute('disabled', true);
                    showError('reCAPTCHA has expired. Please refresh the page.');
                }
            });
            
            window.recaptchaVerifier.render();
        }

        // Check if phone number exists
        async function checkPhoneNumberExists(phoneNumber) {
            try {
                const response = await fetch('/auth/verify-phone', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ phone_number: phoneNumber })
                });
                
                const data = await response.json();
                return data.exists;
            } catch (error) {
                console.error('Error checking phone number:', error);
                return false;
            }
        }

        // Send OTP
        async function sendOTP() {
            const phoneNumber = phoneNumberInput.value.trim();
            
            if (!phoneNumber) {
                showError('Please enter a valid phone number', 'phone-error');
                return;
            }
            
            clearError('phone-error');
            
            try {
                // Show loader
                sendOtpBtn.setAttribute('disabled', true);
                sendOtpLoader.classList.remove('hidden');
                sendOtpText.textContent = 'Sending...';
                
                // Check if phone number exists in the database
                const exists = await checkPhoneNumberExists(phoneNumber);
                
                if (!exists) {
                    showError('This phone number is not registered. Please contact an administrator.', 'phone-error');
                    sendOtpBtn.removeAttribute('disabled');
                    sendOtpLoader.classList.add('hidden');
                    sendOtpText.textContent = 'Send OTP';
                    return;
                }
                
                // Request OTP
                const appVerifier = window.recaptchaVerifier;
                confirmationResult = await signInWithPhoneNumber(auth, phoneNumber, appVerifier);
                
                // Show verification code step
                phoneLoginStep1.classList.add('hidden');
                phoneLoginStep2.classList.remove('hidden');
                
            } catch (error) {
                console.error('Error sending OTP:', error);
                showError(error.message || 'Failed to send verification code. Please try again.', 'phone-error');
                
                // Reset reCAPTCHA
                window.recaptchaVerifier.render().then(function(widgetId) {
                    grecaptcha.reset(widgetId);
                });
            } finally {
                // Hide loader
                sendOtpBtn.removeAttribute('disabled');
                sendOtpLoader.classList.add('hidden');
                sendOtpText.textContent = 'Send OTP';
            }
        }

        // Verify OTP
        async function verifyOTP() {
            const code = verificationCodeInput.value.trim();
            
            if (!code || code.length !== 6) {
                showError('Please enter a valid 6-digit verification code', 'verification-error');
                return;
            }
            
            clearError('verification-error');
            
            try {
                // Show loader
                verifyOtpBtn.setAttribute('disabled', true);
                verifyOtpLoader.classList.remove('hidden');
                verifyOtpText.textContent = 'Verifying...';
                
                // Verify code
                const result = await confirmationResult.confirm(code);
                const user = result.user;
                const idToken = await user.getIdToken();
                
                // Send token to server
                await verifyTokenWithServer(idToken);
                
            } catch (error) {
                console.error('Error verifying OTP:', error);
                showError(error.message || 'Invalid verification code. Please try again.', 'verification-error');
            } finally {
                // Hide loader
                verifyOtpBtn.removeAttribute('disabled');
                verifyOtpLoader.classList.add('hidden');
                verifyOtpText.textContent = 'Verify OTP';
            }
        }

        // Google Sign In
        async function signInWithGoogle() {
            try {
                const provider = new GoogleAuthProvider();
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                const idToken = await user.getIdToken();
                
                // Send token to server
                await verifyTokenWithServer(idToken, 'google');
                
            } catch (error) {
                console.error('Error signing in with Google:', error);
                showError(error.message || 'Google sign-in failed. Please try again.');
            }
        }

        // Verify token with server
        async function verifyTokenWithServer(idToken, provider = 'phone') {
            try {
                const response = await fetch('/auth/verify-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ idToken, provider })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to dashboard or appropriate page
                    window.location.href = data.redirect || '/dashboard';
                } else {
                    showError(data.message || 'Authentication failed.');
                }
            } catch (error) {
                console.error('Error verifying token with server:', error);
                showError('Server error. Please try again later.');
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize reCAPTCHA
            initRecaptcha();
            
            // Phone Login Button
            phoneLoginBtn.addEventListener('click', function() {
                loginOptions.classList.add('hidden');
                phoneLoginForm.classList.remove('hidden');
                phoneLoginStep1.classList.remove('hidden');
                phoneLoginStep2.classList.add('hidden');
                clearError();
                clearError('phone-error');
            });
            
            // Google Login Button
            googleLoginBtn.addEventListener('click', function() {
                clearError();
                signInWithGoogle();
            });
            
            // Back to Login Options
            backToLoginBtn.addEventListener('click', function() {
                phoneLoginForm.classList.add('hidden');
                loginOptions.classList.remove('hidden');
                clearError();
                clearError('phone-error');
            });
            
            // Back to Phone Input
            backToPhoneBtn.addEventListener('click', function() {
                phoneLoginStep2.classList.add('hidden');
                phoneLoginStep1.classList.remove('hidden');
                clearError();
                clearError('verification-error');
            });
            
            // Send OTP Button
            sendOtpBtn.addEventListener('click', sendOTP);
            
            // Verify OTP Button
            verifyOtpBtn.addEventListener('click', verifyOTP);
            
            // Enter key for verification code
            verificationCodeInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    verifyOTP();
                }
            });
        });
    </script>
</body>
</html> 