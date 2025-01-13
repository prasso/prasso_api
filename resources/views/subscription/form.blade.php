<x-app-layout :site="$site ?? null">

<x-slot name="title">Subscribe</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subscribe') }}
        </h2>
    </x-slot>

<!-- Include Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Inter:,i,b&amp;display=block"/>
<style>
  /* Base Styles */
  @layer base {
    * {
      @apply border-border;
    }
    html {
      @apply scroll-smooth;
    }
    body {
      font-synthesis: none;
      text-rendering: optimizeLegibility;
      font-family: 'Inter', sans-serif;
    }
  }

  /* Utility Classes */
  @layer utilities {
    .step {
      counter-increment: step;
    }
    .step:before {
      @apply absolute w-9 h-9 bg-muted rounded-full font-mono font-medium text-center text-base inline-flex items-center justify-center -indent-px border-4 border-background;
      content: counter(step);
      margin-left: -50px;
      margin-top: -4px;
    }
    .chunk-container {
      @apply shadow-none;
    }
    .chunk-container::after {
      content: '';
      @apply absolute -inset-4 shadow-xl rounded-xl border;
    }
  }

  /* Responsive Styles */
  @media (max-width: 640px) {
    .container {
      @apply px-4;
    }
  }

  /* Theme Variables */
  :root {
    --background: 0 0% 100%;
    --foreground: 240 10% 3.9%;
    --card: 0 0% 100%;
    --card-foreground: 240 10% 3.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 240 10% 3.9%;
    --primary: 240 5.9% 10%;
    --primary-foreground: 0 0% 98%;
    --secondary: 240 4.8% 95.9%;
    --secondary-foreground: 240 5.9% 10%;
    --muted: 240 4.8% 95.9%;
    --muted-foreground: 240 3.8% 45%;
    --accent: 240 4.8% 95.9%;
    --accent-foreground: 240 5.9% 10%;
    --destructive: 0 72% 51%;
    --destructive-foreground: 0 0% 98%;
    --border: 240 5.9% 90%;
    --input: 240 5.9% 90%;
    --ring: 240 5.9% 10%;
    --chart-1: 173 58% 39%;
    --chart-2: 12 76% 61%;
    --chart-3: 197 37% 24%;
    --chart-4: 43 74% 66%;
    --chart-5: 27 87% 67%;
    --radius: 0.5rem;
  }

  /* Image Filters */
  img[src="/placeholder.svg"],
  img[src="/placeholder-user.jpg"] {
    filter: sepia(.3) hue-rotate(-60deg) saturate(.5) opacity(0.8);
  }

  .spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #000;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-left: 8px;
}

.hidden {
    display: none;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

</style>
    
<form id="payment-form">


<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($products as $planId => $productName)
                @if($planId !== 'none')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200 relative plan-card cursor-pointer transition-all duration-200 hover:shadow-lg flex flex-col h-full" 
                         data-plan-id="{{ $planId }}"
                         onclick="selectPlan(event, '{{ $planId }}', '{{ $productName }}')">
                        
                        <!-- Icons for each plan -->
                        <div class="flex justify-center mb-6">
                            @if(str_contains(strtolower($productName), 'bundle'))
                                <svg class="w-16 h-16 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                                </svg>
                            @elseif(str_contains(strtolower($productName), 'developer'))
                                <svg class="w-16 h-16 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                            @else
                                <svg class="w-16 h-16 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            @endif
                        </div>
                        
                        <h2 class="text-3xl font-bold mb-2 text-center">{{ $productName }}</h2>
                        
                        <div class="flex-grow"></div>
                        
                        <button class="w-full bg-gray-800 text-white rounded-md py-2 px-4 hover:bg-gray-700 transition-colors select-plan-btn mt-auto"
                                data-plan-id="{{ $planId }}">
                           Select plan
                        </button>
                        
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- Hidden select element for compatibility with existing JS -->
<select id="subscription-product-selection" name="subscription_product" class="hidden">
    @foreach($products as $planId => $productName)
        <option value="{{ $planId }}">{{ $productName }}</option>
    @endforeach
</select>

<div class="bg-white rounded-lg border bg-card text-card-foreground shadow-sm w-full max-w-md mx-auto my-20" data-id="1" data-v0-t="card">
    <div id="feedback-message" class="hidden p-4 mb-4 text-sm text-white bg-green-500 rounded-md"></div>
    <div class="flex flex-col space-y-1.5 p-6" data-id="2">
        <h3 class="whitespace-nowrap text-2xl font-semibold leading-none tracking-tight" data-id="3">Payment Details</h3>
        <p class="text-sm text-muted-foreground" data-id="4">Enter your payment information below.</p>
        
        <!-- Selected Plan Display -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm font-medium text-gray-600">Selected Plan:</p>
            <p id="selected-plan-display" class="text-lg font-semibold text-gray-900">No plan selected</p>
        </div>
    </div>
    <div class="p-6 space-y-4" data-id="5">
        <div>
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="subscription-card-holder-name">Cardholder Name</label><br>
            <input id="subscription-card-holder-name" type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
        </div>
        <div id="subscription-card-element"></div>
        
        <div class="flex items-center p-6" data-id="35">
            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full border" type="submit" data-id="36" id="subscription-button" data-secret="{{ $setupIntent->client_secret }}">
                Subscribe
            </button>
        </div>
    </div>
</div>

<!-- Add the "Powered by Stripe" badge -->
<div class="text-center mb-8">
    <a href="https://stripe.com" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" height="25" width="auto" viewBox="-17.85 -6.5 154.7 39">
            <path d="M113 26H6c-3.314 0-6-2.686-6-6V6c0-3.314 2.686-6 6-6h107c3.314 0 6 2.686 6 6v14c0 3.314-2.686 6-6 6z"/>
        </svg>
    </a>
</div>

<script>
    function selectPlan(event, planId, productName) {
        // Prevent default behavior and event propagation
        event.preventDefault();
        event.stopPropagation();
        
        // Remove selected state from all cards
        document.querySelectorAll('.plan-card').forEach(card => {
            card.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50');
            const btn = card.querySelector('.select-plan-btn');
            btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
            btn.classList.add('bg-gray-800', 'hover:bg-gray-700');
        });

        // Add selected state to clicked card
        const selectedCard = document.querySelector(`[data-plan-id="${planId}"]`);
        selectedCard.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
        const selectedBtn = selectedCard.querySelector('.select-plan-btn');
        selectedBtn.classList.remove('bg-gray-800', 'hover:bg-gray-700');
        selectedBtn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');

        // Update hidden select element
        document.getElementById('subscription-product-selection').value = planId;
        
        // Update the selected plan display
        document.getElementById('selected-plan-display').textContent = productName;

        // Focus the cardholder name field
        document.getElementById('subscription-card-holder-name').focus();
        
        // Scroll the payment form into view smoothly
        document.getElementById('subscription-card-holder-name').scrollIntoView({ behavior: 'smooth' });
    }

    // Initialize Stripe using the Stripe key from the Site model
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements();

    // Define card element styling
    const cardStyle = {
        base: {
            backgroundColor: '#f6f6f6',
            fontFamily: 'Arial, sans-serif',
            fontSize: '16px',
            color: '#32325d',
            '::placeholder': {
                color: '#aab7c4',
            },
            padding: '10px',
            border: '1px solid #ced4da',
            borderRadius: '4px',
            boxShadow: 'inset 0 1px 1px rgba(0, 0, 0, 0.075)',
        },
        invalid: {
            color: '#d9534f',
            iconColor: '#d9534f',
        },
        complete: {
            color: '#5cb85c',
            iconColor: '#5cb85c',
        },
    };

    // Create and mount the card element
    const subscriptionCardElement = elements.create('card', { style: cardStyle });
    subscriptionCardElement.mount('#subscription-card-element');

    // Get references to necessary DOM elements
    const subscriptionButton = document.getElementById('subscription-button');
    const feedbackMessage = document.getElementById('feedback-message');
    const subscriptionClientSecret = subscriptionButton.dataset.secret;

    // Create and append spinner to the subscription button
    const spinner = document.createElement('span');
    spinner.classList.add('spinner', 'hidden');
    subscriptionButton.appendChild(spinner);

    // Handle subscription button click event
    subscriptionButton.addEventListener('click', async (e) => {
        e.preventDefault();
        toggleSubscriptionButtonState(true);

        feedbackMessage.classList.add('hidden');

        try {
            const { setupIntent, error } = await stripe.confirmCardSetup(subscriptionClientSecret, {
                payment_method: {
                    card: subscriptionCardElement,
                    billing_details: {
                        name: document.getElementById('subscription-card-holder-name').value,
                    },
                },
            });

            if (error) {
                handleError(`Error: ${error.message}`);
            } else {
                await handleSubscriptionSuccess(setupIntent.payment_method);
            }
        } catch (error) {
            handleError("An unexpected error occurred.");
            console.error(error);
        } finally {
            toggleSubscriptionButtonState(false);
        }
    });

    // Helper function to toggle the subscription button's state
    function toggleSubscriptionButtonState(isProcessing) {
        subscriptionButton.disabled = isProcessing;
        subscriptionButton.innerHTML = isProcessing ? 'Processing...' : 'Subscribe';
        spinner.classList.toggle('hidden', !isProcessing);
    }

    // Handle errors by displaying feedback and resetting the button state
    function handleError(message) {
        feedbackMessage.textContent = message;
        feedbackMessage.classList.remove('hidden', 'bg-green-500');
        feedbackMessage.classList.add('bg-red-500');
    }

    // Handle successful subscription
    async function handleSubscriptionSuccess(paymentMethod) {
        try {
            const response = await axios.post('/subscribe', {
                payment_method: paymentMethod,
                subscription_product: document.getElementById('subscription-product-selection').value,
            });

            feedbackMessage.textContent = "Subscription successful!";
            feedbackMessage.classList.remove('hidden', 'bg-red-500');
            feedbackMessage.classList.add('bg-green-500');

            // Redirect to /dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 2000);
        } catch (error) {
            handleError("An error occurred during subscription.");
            console.error(error);
        }
    }
</script>


</x-app-layout>
