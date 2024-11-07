<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SitePageData;

class PrassoFlipper extends Component
{
    public $slides=[];

    public function mount($pageid=null)
    {

        // this livewire component can be used from an html site page by embedding [CAROUSEL_COMPONENT] in an html element.
        /* example
        <article>    
            [CAROUSEL_COMPONENT]
        </article>
       
        /// Define each slide's content here. You can customize these as needed using SitePageData. save this slides array
        // as a json object, with fk_site_page_id equal to the page id you have embedded the tag, [CAROUSEL_COMPONENT]
        $this->slides = [
            '<section class="palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left" style="height: 389px;">
                <div class="palette content-wrapper flex items-center h-full">
                    <div class="flex-1">
                        <header><div class="slide-header mb-3"><p>Welcome</p></div></header>
                        <div class="text-content text-2 mb-4">Join us for Sunday service every week with Bible Study at 10 AM and Worship Service at 11:00 AM. All are welcome to experience worship and community.</div>
                        <button class="px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200">
                            Learn More
                        </button>
                    </div>
                    <div class="flex-1">
                        <a href="/page/About" class="block w-full h-full">
                            <img src="https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png"
                                srcset="https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x"
                                alt="Church Image" class="rounded h-full object-cover">
                        </a>
                    </div>
                </div>
            </section>
            ',
        
            "<section class='palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left' style='height: 389px;'>
                    <div class='palette content-wrapper flex items-center h-full'>
                        <div class='flex-1'>
                            <header><div class='slide-header mb-3'><p>Community Events</p></div></header>
                            <div class='text-content text-lg mb-6'>Discover our upcoming events and gatherings that strengthen our community and bring us together in fellowship.</div>
                            <a href='/page/events' class='mt-4 px-6 py-2 bg-white text-green-600 rounded-lg font-semibold hover:bg-gray-200 transition ease-in-out duration-200 inline-block'>View Events</a>
                        </div>
                        <div class='flex-1'>
                            <a href='/page/About' class='block w-full h-full'>
                                <img src='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png'
                                    srcset='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x'
                                    alt='Church Image' class='rounded h-full object-cover'>
                            </a>
                        </div>
                    </div>
                </section>
                ",
                        
                "<section class='palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left' style='height: 389px;'>
                    <div class='palette content-wrapper flex items-center h-full'>
                        <div class='flex-1'>
                            <header><div class='slide-header mb-3'><p>Bible Study</p></div></header>
                            <div class='text-content text-2 mb-4'>Join us every week for Bible study as we dive deeper into scripture and grow in our faith together.</div>
                            <button class='px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200'>
                                Join a Group
                            </button>
                        </div>
                        <div class='flex-1'>
                            <a href='/page/About' class='block w-full h-full'>
                                <img src='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png'
                                    srcset='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x'
                                    alt='Church Image' class='rounded h-full object-cover'>
                            </a>
                        </div>
                    </div>
                </section>
                ",
                        
                    "<section class='palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left' style='height: 389px;'>
            <div class='palette content-wrapper flex items-center h-full'>
                <div class='flex-1'>
                    <header><div class='slide-header mb-3'><p>Children's Ministry</p></div></header>
                    <div class='text-content text-2 mb-4'>Our church offers a vibrant program for children where they can learn, play, and grow in a safe and joyful environment.</div>
                    <button class='px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200'>
                        Learn More
                    </button>
                </div>
                <div class='flex-1'>
                    <a href='/page/About' class='block w-full h-full'>
                        <img src='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png'
                            srcset='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x'
                            alt='Church Image' class='rounded h-full object-cover'>
                    </a>
                </div>
            </div>
        </section>
        ",
        
            "<section class='palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left' style='height: 389px;'>
    <div class='palette content-wrapper flex items-center h-full'>
        <div class='flex-1'>
            <header><div class='slide-header mb-3'><p>Building Improvement Project</p></div></header>
            <div class='text-content text-2 mb-4'>Support our ministry through secure online giving and help us continue our work and mission in the community.</div>
            <button class='px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200'>
                Donate Now
            </button>
        </div>
        <div class='flex-1'>
            <a href='/page/About' class='block w-full h-full'>
                <img src='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png'
                    srcset='https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x'
                    alt='Church Image' class='rounded h-full object-cover'>
            </a>
        </div>
    </div>
</section>
",
        ]; */
        
        if ($pageid){
               // Fetch the json_data as a string
            $jsonDataString = SitePageData::where('fk_site_page_id', $pageid)->first()->json_data;

            // Decode the JSON string to a PHP array
            $this->slides = json_decode($jsonDataString, true);  // 'true' makes it an associative array
            
            // Now $slides is an array and you can work with it
        }
    }

    public function render()
    {
        return view('livewire.prasso-flipper');
    }
}
