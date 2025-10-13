<header class="border-b bg-white sticky top-0 z-50">
    <div class="container flex items-center justify-between h-16 px-4 md:px-6">
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="flex items-center mr-8">
                <div class="w-8 h-8 bg-red-600 mr-2"></div>
                <span class="text-xl font-bold">DigiLearn</span>
            </a>
            <nav class="hidden md:flex gap-6">
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-800">Grades</a>
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-800">Subjects</a>
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-800">Quizzes</a>
                <a href="{{ route('about') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">About Us</a>
                <a href="{{ route('contact') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Contact</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <a href="#" class="rounded-full hidden md:flex px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 text-sm font-medium">
                Login
            </a>
            <a href="#" class="rounded-full bg-red-600 hover:bg-red-700 px-4 py-2 text-white text-sm font-medium">
                Sign Up Free
            </a>
        </div>
    </div>
</header>
