<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Password Reset</h1>
                <p class="text-blue-100">Secure your ProperQuant account</p>
            </div>

            <!-- Content -->
            <div class="px-8 py-10">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Hello {{ $userName }},</h2>
                    <p class="text-gray-600 leading-relaxed">
                        We received a request to reset your password for your ProperQuant account. 
                        Click the button below to create a new password and regain access to your account.
                    </p>
                </div>

                <!-- CTA Button -->
                <div class="text-center mb-10">
                    <a href="{{ $resetUrl }}" 
                       class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2l-4-4L7.257 8.257A6 6 0 0119 9z"/>
                        </svg>
                        Reset My Password
                    </a>
                </div>

                <!-- Token Info -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Reset Token</h3>
                            <p class="text-sm font-mono text-gray-600 bg-white px-3 py-2 rounded border break-all">
                                {{ $resetToken }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Expiry Notice -->
                <div class="flex items-center justify-center space-x-2 mb-8">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-600 font-medium">
                        Expires {{ $expiresAt->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>

                <!-- Security Notice -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-amber-800 mb-1">Security Notice</h3>
                            <p class="text-sm text-amber-700">
                                If you didn't request this password reset, please ignore this email. 
                                Your password will remain unchanged and your account stays secure.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Alternative Link -->
                <div class="text-center text-sm text-gray-500">
                    <p class="mb-2">Having trouble with the button?</p>
                    <p>Copy and paste this link into your browser:</p>
                    <a href="{{ $resetUrl }}" class="text-blue-600 hover:text-blue-800 font-mono text-xs break-all">
                        {{ $resetUrl }}
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-200 px-8 py-8 text-center">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">ProperQuant</h3>
                    <p class="text-sm text-gray-600">Real Estate Investment Management Platform</p>
                </div>
                
                <div class="flex justify-center space-x-6 mb-4">
                    <a href="https://properquant.net" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Website
                    </a>
                    <a href="mailto:support@properquant.net" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Support
                    </a>
                </div>
                
                <div class="text-xs text-gray-500">
                    <p>&copy; {{ date('Y') }} ProperQuant. All rights reserved.</p>
                    <p class="mt-1">This email was sent to {{ $userName }} regarding password reset.</p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
