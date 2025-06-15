<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#6366f1',
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-scale { transition: transform 0.2s ease; }
        .hover-scale:hover { transform: scale(1.02); }
    </style>
</head>
<body class="bg-gray-50">
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
        <div id="flash-message" class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg <?php echo $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
            <div class="flex items-center">
                <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
                <button onclick="document.getElementById('flash-message').remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const flashMessage = document.getElementById('flash-message');
                if (flashMessage) flashMessage.remove();
            }, 5000);
        </script>
    <?php endif; ?>