<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご購入ありがとうございます</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0 auto;
            max-width: 1400px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container mx-auto p-8 bg-white rounded-xl shadow-lg max-w-lg text-center">
        <div class="text-purple-500 mb-6">
            <!-- サイズと位置を合わせるためのSVGアイコン -->
            <svg class="h-24 w-24 mx-auto" fill="currentColor" stroke="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-4 text-gray-800">ご出品ありがとうございます</h1>
        <p class="text-gray-600 mb-8">お客様の出品手続きは正常に完了しました。</p>
        <a href="{{ route('front_page') }}" class="inline-block bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors">
            トップページに戻る
        </a>
    </div>
</body>
</html>