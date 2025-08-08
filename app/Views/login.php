<?= view('templates/header') ?>

<div class="flex min-h-screen bg-[#F5ECFE]">
    <div class="flex flex-col justify-center items-center flex-1 px-8 py-8">
        <div class="text-4xl font-bold mb-10 text-gray-800 font-nunito">Perfect Smilessssssss :)</div>
        <form class="w-full max-w-md flex flex-col gap-6 bg-white/0">
            <div>
                <label for="email" class="font-semibold text-gray-800 mb-2 block">Email address</label>
                <input type="email" id="email" placeholder="Enter your email" required
                    class="border-2 border-[#c7aefc] rounded-lg px-4 py-3 text-base bg-[#f9f6ff] text-gray-800 outline-none transition-colors duration-200 w-full focus:border-[#a47be5] mb-1">
            </div>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="password" class="font-semibold text-gray-800 mb-0">Password</label>
                    <a href="#" class="text-sm text-indigo-500 font-medium hover:underline ml-4">forgot password</a>
                </div>
                <input type="password" id="password" placeholder="Password" required
                    class="border-2 border-[#c7aefc] rounded-lg px-4 py-3 text-base bg-[#f9f6ff] text-gray-800 outline-none transition-colors duration-200 w-full focus:border-[#a47be5] mb-1">
            </div>
            <div class="flex items-center gap-2 mb-1">
                <input type="checkbox" id="remember" class="accent-[#c7aefc]">
                <label for="remember" class="mb-0">Remember Me</label>
            </div>
            <button type="submit"
                class="bg-[#c7aefc] hover:bg-[#a47be5] text-white rounded-lg py-3 text-lg font-bold w-full mt-2 transition-colors duration-200">Login</button>
        </form>
    </div>
    <div class="flex-1 hidden lg:flex items-center justify-center min-h-screen bg-white rounded-tl-[40px] rounded-bl-[40px]"
        style="background-image: url('<?= base_url('img/bg.jpg') ?>'); background-position: center; background-size: cover;">
        <!-- Image goes here later -->
    </div>
</div>

<?= view('templates/footer') ?>
