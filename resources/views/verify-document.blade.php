{{-- resources/views/verify-document.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full" id="app">
            <div class="text-center mb-8">
                <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Document Verification</h2>
                <p class="mt-2 text-gray-600">Verifying document authenticity...</p>
            </div>

            <div id="verification-result" class="hidden">
                <!-- Result will be populated by JavaScript -->
            </div>

            <div id="loading" class="text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                <p class="mt-2 text-gray-600">Please wait...</p>
            </div>

            <div id="error" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700" id="error-message"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const token = window.location.pathname.split('/').pop();
        
        fetch(`/api/verify-document/${token}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').classList.add('hidden');
                
                if (data.success) {
                    displaySuccess(data.data);
                } else {
                    displayError(data.message);
                }
            })
            .catch(error => {
                document.getElementById('loading').classList.add('hidden');
                displayError('An error occurred while verifying the document');
            });

        function displaySuccess(data) {
            const resultDiv = document.getElementById('verification-result');
            resultDiv.classList.remove('hidden');
            
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Document Verified Successfully</h3>
                        </div>
                    </div>
                </div>

                <div class="border rounded-md divide-y">
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Document Title</p>
                        <p class="text-sm text-gray-900">${data.document.title}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Document Number</p>
                        <p class="text-sm text-gray-900">${data.document.document_number}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Organization</p>
                        <p class="text-sm text-gray-900">${data.organization.name}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Category</p>
                        <p class="text-sm text-gray-900">${data.category ? data.category.name : 'N/A'}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Published Date</p>
                        <p class="text-sm text-gray-900">${data.metadata.published_at || 'N/A'}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Expiry Date</p>
                        <p class="text-sm text-gray-900">${data.metadata.expiry_date || 'No Expiry'}</p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-500">Verified At</p>
                        <p class="text-sm text-gray-900">${data.metadata.verified_at}</p>
                    </div>
                </div>

                <div class="mt-4 flex space-x-3">
                    <a href="${data.preview_url}" target="_blank" class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                        Preview Document
                    </a>
                    <a href="${data.download_url}" class="flex-1 bg-gray-600 text-white text-center px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                        Download
                    </a>
                </div>
            `;
        }

        function displayError(message) {
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('error-message').textContent = message;
        }
    </script>
</body>
</html>