
    <div class="items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">

<section class="text-gray-700 body-font">
    <div class="container px-8 pt-20 pb-4 mx-auto lg:px-4">
        <div class="flex flex-col w-full mb-12 text-left lg:text-center">
            <h2 class="mb-1 font-semibold tracking-widest text-blue-600 uppercase title-font">
            Your No-Code solution<br class="md:hidden">
                to an app for your business.</h2>
            <h1 class="mb-6 text-2xl font-semibold tracking-tighter text-blue-800 sm:text-6xl title-font">
            Prasso 
            </h1>
            
            <p class="mx-auto text-base font-medium leading-relaxed text-gray-700 lg:w-2/3">
            Fast API eXtraction Technologies - faxt - presents Prasso. 
            Prasso is an easy way to create your own app. Use these tools to assemble your personalized
            mobile app, then add your team to provide access.
            With Prasso, you designate which tabs to include in a mobile application and who can use it.</p>
             <p>Prasso setup enables you to set up your mobile app with no code required.</p>
        </div>
        @if (auth()->user() == null)
        <div class="flex lg:justify-center">
            <a href="/login"
                class="px-8 py-2 font-semibold text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl bg-gradient-to-r from-blue-700 hover:from-blue-600 to-blue-600 hover:to-blue-700 focus:ring focus:outline-none">Sign In</a>
        </div>
        @endif
    </div>
</section>
<section class="block " id="section">
    <div class="container flex flex-col items-center justify-center px-10 py-2 mx-auto lg:px-48">
        <img class="object-cover object-center mx-auto mb-10 rounded-lg lg:w-full md:w-15/5 w-20/6" alt="hero"
            src="/images/butterfly.png">
    </div>
</section>
</div>  
  