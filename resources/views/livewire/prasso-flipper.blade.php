
<div class="relative w-full overflow-hidden bg-white" x-data="{ currentSlide: 0, slideCount: {{ count($slides) }} }"
     x-init="setInterval(() => {
                 currentSlide++;
                 if (currentSlide >= slideCount) {
                     // Briefly disable transition, reset slide position, then re-enable
                     $el.querySelector('.carousel-content').classList.add('no-transition');
                     currentSlide = 0;
                     setTimeout(() => $el.querySelector('.carousel-content').classList.remove('no-transition'), 20);
                 }
             }, 5000)">
    <!-- Carousel Content -->
    <div class="carousel-content w-full flex transition-transform duration-500 ease-in-out"
         :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
        @foreach ($slides as $slide)
            <div class="w-full flex-shrink-0 px-4 py-10 text-center" style="min-height: 250px;">
                {!! $slide !!}
            </div>
        @endforeach
    </div>
   



    <!-- Navigation Dots -->
    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 flex space-x-2 pb-4">
        @foreach ($slides as $index => $slide)
            <button
                class="w-3 h-3 rounded-full focus:outline-none"
                :class="{'bg-blue-500': currentSlide === {{ $index }}, 'bg-gray-400': currentSlide !== {{ $index }}}"
                @click="currentSlide = {{ $index }}"></button>
        @endforeach
    </div>
    <style>
    /* CSS to remove the transition temporarily */
    .no-transition {
        transition: none !important;
    }
</style>

</div>
