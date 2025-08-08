<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-In Error - Perfect Smile Dental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-500 to-orange-500 p-8 text-center">
                <div class="text-white">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                    <h1 class="text-2xl font-bold">Perfect Smile Dental</h1>
                    <p class="text-red-100 mt-2">Check-In Error</p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-6">
                    <i class="fas fa-times text-2xl text-red-600"></i>
                </div>
                
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Unable to Check In</h2>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-800"><?= esc($message) ?></p>
                </div>

                <div class="space-y-4">
                    <p class="text-gray-600">
                        Please contact the reception desk for assistance with your check-in.
                    </p>
                    
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">What you can do:</h3>
                        <ul class="text-sm text-blue-700 space-y-1 text-left">
                            <li><i class="fas fa-check mr-2"></i>Approach the reception desk</li>
                            <li><i class="fas fa-check mr-2"></i>Verify your appointment details</li>
                            <li><i class="fas fa-check mr-2"></i>Check if you're at the right clinic</li>
                            <li><i class="fas fa-check mr-2"></i>Confirm your appointment date and time</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center">
                <p class="text-xs text-gray-500">
                    Thank you for choosing Perfect Smile Dental
                </p>
            </div>
        </div>
    </div>
</body>
</html>
