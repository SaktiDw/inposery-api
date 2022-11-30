<nav class="fixed top-0 left-0 z-50 w-full px-6 py-3 flex items-center justify-between bg-slate-100 bg-opacity-50 backdrop-blur dark:bg-slate-900 dark:backdrop-blur-lg dark:bg-opacity-50">
    <div>
        <button class="p-3" x-on:click="$dispacth('toggle')">
            <i class="fi-rr-menu-burger"></i>
        </button>
        <span class="font-bold text-2xl">
            In<span class="text-lime-500">POS</span>ery
        </span>
    </div>

    <div class="flex gap-2 ">
        @auth
        <span class="cursor-pointer">
            {{auth()->user()->email}}
        </span>
        @endauth

        @guest
        <a href={"/login"}>Login</a>
        <a href={"/register"}>Register</a>
        @endguest
    </div>
</nav>