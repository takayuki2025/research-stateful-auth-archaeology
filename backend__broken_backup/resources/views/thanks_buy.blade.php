<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご購入ありがとうございます</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            margin: 0 auto;
            max-width: 1400px;
        }
    </style>
</head>
<body class="bg-gray-100 p-8 flex items-center justify-center min-h-screen">
    <div class="container mx-auto p-8 bg-white rounded-xl shadow-xl ring-4 ring-gray-300 ring-inset max-w-lg text-center">

        <div class="text-green-500 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

                @if (session('success_conbini'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{!! session('success_conbini') !!}</span>
            </div>
        @endif
        <h1 class="text-3xl font-bold mb-4 text-gray-800">ご購入ありがとうございます</h1>
        <p class="text-gray-600 mb-8">お客様の購入は正常に完了しました。</p>
        <a href="{{ route('front_page') }}" class="inline-block bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors">
            トップページに戻る
        </a>
    </div>
</body>
</html>