<x-app-layout :site="$site ?? null">
    <x-slot name="title">Donate</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Donate') }}
        </h2>
    </x-slot>

    <div class="container max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Error Message Div -->
        <div id="error-message" class="hidden bg-red-100 text-red-800 p-4 rounded-lg shadow-md mb-4">
            <p class="font-semibold">There was an error processing your payment:</p>
            <p id="error-text" class="mt-2"></p>
        </div>
        
       <!-- Thank You Message Div -->
        <div id="thank-you-message" class="hidden bg-green-100 text-green-800 p-6 rounded-lg shadow-md">
            <h3 class="text-2xl font-semibold">Thank you for your donation!</h3>
            <p class="text-lg mt-4">We appreciate your generosity. Here is a summary of your donation:</p>
            <ul class="mt-4">
                <li>Subtotal: $<span id="thank-you-subtotal">0.00</span></li>
                <li id="stripe-fee-li" class="hidden">Stripe Fee: $<span id="thank-you-fee">0.00</span></li>
                <li>Total Charged: $<span id="thank-you-total">0.00</span></li>
            </ul>
        </div>


        <form id="donation-form" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Make a Donation</h2>

            <!-- Cover Stripe Fees Radio Buttons -->
            <div class="mb-6">
                <label class="block text-lg font-medium text-gray-700 mb-2">Cover Stripe Fees</label>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="radio" id="cover-fee-yes" name="cover_fee" value="yes" checked class="cover-fee-radio">
                        <label for="cover-fee-yes" class="ml-2 text-gray-700">Yes, add 2.9% + $0.30</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="cover-fee-no" name="cover_fee" value="no" class="cover-fee-radio">
                        <label for="cover-fee-no" class="ml-2 text-gray-700">No, don't add fee</label>
                    </div>
                </div>
            </div>

            <!-- Card holder name -->
            <div class="mb-4">
                <label for="card-holder-name" class="block text-lg font-medium text-gray-700 mb-1">Card Holder Name</label>
                <input id="card-holder-name" type="text" placeholder="Enter your name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800">
            </div>

            <!-- Stripe card element -->
            <div id="card-element" class="mt-4 mb-4 p-4 border border-gray-300 rounded-md"></div>

            <!-- Donation items -->
            <h3 class="text-2xl font-semibold text-gray-700 mt-8 mb-4">Select Donation Items</h3>
            <div id="donation-items" class="space-y-4">
                @foreach($donationItems as $id => $name)
                <div class="donation-item flex items-center space-x-4">
                    <input type="checkbox" class="donation-checkbox h-5 w-5 text-blue-600" data-id="{{ $id }}">
                    <label class="text-lg text-gray-700">{{ $name }}</label>
                    <input type="number" class="donation-amount w-24 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        data-id="{{ $id }}" min="1" step="1" placeholder="Amount" disabled>
                </div>
                @endforeach
            </div>

            <!-- Total Donation Amount -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h4 class="text-xl font-semibold text-gray-800">Donation Summary:</h4>
                <div class="mt-2">
                    <div class="flex justify-between text-gray-700">
                        <span>Sub-total:</span>
                        <span>$<span id="sub-total">0.00</span></span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Stripe Fee:</span>
                        <span>$<span id="stripe-fee">0.00</span></span>
                    </div>
                    <div class="flex justify-between text-gray-900 font-bold mt-2">
                        <span>Total Church Receives:</span>
                        <span>$<span id="total-church-receives">0.00</span></span>
                    </div>
                    <div class="flex justify-between text-gray-900 font-bold mt-2">
                        <span>Your Total Donation:</span>
                        <span>$<span id="total-donation">0.00</span></span>
                    </div>
                    <input type="hidden" id="total-donation-input" name="total_donation" value="0">
                </div>
            </div>

            <!-- Submit button -->
            <button id="submit-button" type="submit" disabled
                class="teambutton w-full mt-6 bg-blue-600 text-white font-bold py-3 rounded-md shadow hover:bg-blue-700 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                Donate
            </button>

            <!-- Stripe Badge -->
            <div style="margin-top: 20px;">
                <a href="https://stripe.com" target="_blank">
                    <!-- Stripe SVG -->
                </a>
            </div>
        </form>
               <!-- Add the "Powered by Stripe" badge -->
 <div style="margin-top: 20px;">
        <a href="https://stripe.com" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" height="25" width="auto" viewBox="-17.85 -6.5 154.7 39"><path d="M113 26H6c-3.314 0-6-2.686-6-6V6c0-3.314 2.686-6 6-6h107c3.314 0 6 2.686 6 6v14c0 3.314-2.686 6-6 6zM11.128 8.892H8.571v7.241h1.347v-2.6h1.21c1.474 0 2.526-.947 2.526-2.315 0-1.368-1.052-2.326-2.526-2.326zm5.915 1.853c-1.589 0-2.715 1.136-2.715 2.757 0 1.61 1.126 2.757 2.715 2.757s2.705-1.147 2.705-2.757c0-1.621-1.116-2.757-2.705-2.757zm9.893.126l-1.063 3.578-1.063-3.578h-1.22l-1.063 3.578-1.063-3.578h-1.347l1.81 5.262h1.21l1.063-3.578 1.074 3.578h1.21l1.799-5.262zm4.316-.126c-1.505 0-2.599 1.126-2.599 2.715 0 1.641 1.115 2.799 2.704 2.799.589 0 1.189-.137 1.789-.41v-1.127c-.547.316-1.084.495-1.568.495-.884 0-1.515-.547-1.6-1.347h3.568c.021-.179.021-.347.021-.495 0-1.546-.937-2.63-2.315-2.63zm6.472.052c-.147-.042-.294-.052-.442-.052-.452 0-.915.231-1.294.652v-.526h-1.347v5.262h1.347v-3.515c.347-.442.821-.684 1.263-.684.158 0 .326.021.473.063zm3.084-.052c-1.505 0-2.599 1.126-2.599 2.715 0 1.641 1.116 2.799 2.705 2.799.589 0 1.189-.137 1.789-.41v-1.127c-.548.316-1.084.495-1.569.495-.883 0-1.515-.547-1.599-1.347h3.567c.021-.179.021-.347.021-.495 0-1.546-.936-2.63-2.315-2.63zm8.104-2.179h-1.358v2.663c-.41-.316-.873-.484-1.336-.484-1.4 0-2.378 1.136-2.378 2.757 0 1.62.978 2.757 2.378 2.757.463 0 .926-.168 1.336-.495v.369h1.358zm6.778 2.179c-.452 0-.915.168-1.336.484V8.566h-1.347v7.567h1.347v-.369c.421.327.884.495 1.336.495 1.41 0 2.378-1.137 2.378-2.757 0-1.621-.968-2.757-2.378-2.757zm6.62.126l-1.273 3.452-1.263-3.452h-1.379l2.01 5.072-1.01 2.494H60.7l2.989-7.566zm10.799-.583c.863 0 1.96.264 2.824.731V8.344c-.941-.375-1.881-.519-2.822-.519-2.303 0-3.838 1.203-3.838 3.213 0 3.143 4.316 2.633 4.316 3.988 0 .525-.456.695-1.089.695-.941 0-2.155-.389-3.108-.907v2.711c1.056.454 2.126.644 3.105.644 2.361 0 3.988-1.166 3.988-3.21 0-3.386-4.341-2.778-4.341-4.056 0-.443.37-.615.965-.615zm9.071-2.274h-2.156l-.002-2.466-2.772.59-.013 9.109c0 1.681 1.265 2.922 2.95 2.922.928 0 1.614-.168 1.992-.376v-2.311c-.363.145-2.155.666-2.155-1.007v-4.04h2.156zm6.095.001c-.378-.136-1.705-.384-2.37.838l-.178-.839h-2.455v9.952h2.838v-6.747c.671-.881 1.804-.711 2.165-.594zm3.724-3.785l-2.85.606v2.313l2.85-.606zm0 3.784h-2.85v9.952h2.85zm6.126-.189c-1.113 0-1.832.524-2.224.89l-.148-.701h-2.5l.001 13.254 2.839-.604.006-3.213c.408.299 1.015.718 2.009.718 2.031 0 3.889-1.634 3.889-5.242 0-3.306-1.878-5.102-3.872-5.102zm8.924 0c-2.701 0-4.345 2.296-4.345 5.188 0 3.424 1.938 5.156 4.704 5.156 1.357 0 2.376-.308 3.147-.736v-2.287c-.774.39-1.662.628-2.789.628-1.107 0-2.082-.392-2.209-1.723h5.559c.013-.153.038-.748.038-1.023 0-2.908-1.408-5.203-4.105-5.203zm-.018 2.315c.697 0 1.437.537 1.437 1.815h-2.936c0-1.279.789-1.815 1.499-1.815zm-9.585 5.521c-.666 0-1.063-.24-1.339-.539l-.017-4.219c.296-.325.705-.563 1.356-.563 1.039 0 1.754 1.164 1.754 2.649 0 1.529-.704 2.672-1.754 2.672zm-42.04-.56c-.368 0-.737-.158-1.052-.473v-2.252c.315-.316.684-.474 1.052-.474.758 0 1.284.653 1.284 1.6 0 .947-.526 1.599-1.284 1.599zm-8.893 0c-.769 0-1.295-.652-1.295-1.599s.526-1.6 1.295-1.6c.368 0 .736.158 1.041.474v2.252c-.305.315-.673.473-1.041.473zm-5.757-3.315c.599 0 1.031.495 1.073 1.211h-2.294c.063-.726.568-1.211 1.221-1.211zm-9.557 0c.6 0 1.032.495 1.074 1.211h-2.295c.064-.726.569-1.211 1.221-1.211zm-14.156 3.347c-.789 0-1.336-.663-1.336-1.631s.547-1.631 1.336-1.631c.779 0 1.326.663 1.326 1.631s-.547 1.631-1.326 1.631zm-6.104-2.694H9.918V9.987h1.021c.779 0 1.326.495 1.326 1.231 0 .726-.547 1.221-1.326 1.221z" fill="#424770" opacity=".502" fill-rule="evenodd"/></svg>
        </a>
    </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const stripe = Stripe("{{ $stripeKey }}");
            const elements = stripe.elements();
            const cardElement = elements.create("card");
            cardElement.mount("#card-element");

            const subtotalElement = document.getElementById("sub-total");
            const stripeFeeElement = document.getElementById("stripe-fee");
            const totalDonationElement = document.getElementById("total-donation");
            const totalChurchReceivesElement = document.getElementById("total-church-receives");
            const totalDonationInput = document.getElementById("total-donation-input");
            
            const submitButton = document.getElementById("submit-button");

            const paymentForm = document.getElementById("donation-form");
            const thankYouMessage = document.getElementById("thank-you-message");
            const errorMessageDiv = document.getElementById("error-message");
            const errorText = document.getElementById("error-text");
            const feeRate = 0.029; // Stripe fee rate (2.9%)
            const fixedFee = 0.30; // Fixed Stripe fee in USD

            const coverFeeRadios = document.querySelectorAll(".cover-fee-radio");

            // Update total when cover fee option changes
            coverFeeRadios.forEach(radio => radio.addEventListener("change", updateTotal));

            // Update subtotal when donation items change
            document.querySelectorAll(".donation-checkbox").forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    const amountInput = document.querySelector(`.donation-amount[data-id="${this.dataset.id}"]`);
                    amountInput.disabled = !this.checked;
                    if (!this.checked) {amountInput.value = '';}
                    else {
                        amountInput.focus();
}
                    updateTotal();
                });
            });

            // Get the query parameter "id" from the URL
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get("id");

            if (targetId) {
                // Find the matching checkbox and input field
                const targetCheckbox = document.querySelector(`.donation-checkbox[data-id="${targetId}"]`);
                const targetInput = document.querySelector(`.donation-amount[data-id="${targetId}"]`);

                if (targetCheckbox && targetInput) {
                    // Check the checkbox
                    targetCheckbox.checked = true;

                    // Enable and focus the input field
                    targetInput.disabled = false;
                    targetInput.focus();

                }
            }

            document.querySelectorAll(".donation-amount").forEach(input => {
                input.addEventListener("input", updateTotal);
            });

            submitButton.addEventListener('click', async (event) => {
                event.preventDefault();

                const totalDonation = parseFloat(totalDonationInput.value);
                // Collect selected donation items
                const donationItems = [];
                    document.querySelectorAll('.donation-checkbox:checked').forEach((checkbox) => {
                        const id = checkbox.dataset.id;
                        const amountInput = document.querySelector(`.donation-amount[data-id="${id}"]`);
                        const amount = parseFloat(amountInput.value);
                        if (amount && amount > 0) { // Only add if amount is valid
                            donationItems.push({
                                id: id,
                                amount: amount
                            });
                        }
                    });

                // Get the client secret from the server
                const response = await fetch('/donate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        total_donation: totalDonation,
                        donation_items: donationItems // Include the donation items array
                
                    })
                });

                if (!response.ok) {
                    const errorDetails = await response.json();
                    console.error("Error details:", errorDetails);
                    errorText.innerText = 'An error occurred in preparing to process the payment';
                    errorMessageDiv.classList.remove('hidden');
                    errorMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
                    return;
                }
                errorMessageDiv.classList.add('hidden'); 

                const {
                    client_secret
                } = await response.json();

                // Confirm the payment with Stripe.js using the client secret
                const result = await stripe.confirmCardPayment(client_secret, {
                    payment_method: {
                        card: cardElement, // Use the defined cardElement
                        billing_details: {
                            name: document.getElementById('card-holder-name').value,
                        },
                    },
                });

                if (result.error) {
                    console.error("Payment error:", result.error);
                    errorText.innerText = result.error.message;
                    errorMessageDiv.classList.remove('hidden');

                    errorMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    if (result.paymentIntent.status === 'succeeded') {
                        // Hide the payment form
                        paymentForm.classList.add('hidden');

                        // Show thank-you message and populate it with values
                        document.getElementById('thank-you-subtotal').innerText = subtotalElement.innerText;
                        const coverFee = document.querySelector('input[name="cover_fee"]:checked').value === 'yes';
                        const fee = coverFee ? stripeFeeElement.innerText : 0;
                        if (parseFloat(fee) > 0) {
                            document.getElementById("stripe-fee-li").classList.remove('hidden');
                        } else {
                            document.getElementById("stripe-fee-li").classList.add('hidden');
                        }
                        document.getElementById('thank-you-fee').innerText = fee;
                        document.getElementById('thank-you-total').innerText = totalDonationElement.innerText;

                        // Show the thank-you div
                        thankYouMessage.classList.remove('hidden');

                        thankYouMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            function updateTotal() {
                let subTotal = 0;
                document.querySelectorAll(".donation-amount").forEach(input => {
                    const value = parseFloat(input.value);
                    if (!isNaN(value)) subTotal += value;
                });
                subtotalElement.innerText = subTotal.toFixed(2);

                const coverFee = document.querySelector('input[name="cover_fee"]:checked').value === 'yes';
                let fee = coverFee ? (subTotal * feeRate + fixedFee) :  -(subTotal * feeRate + fixedFee);
                fee = Math.round(fee * 100) / 100;

                stripeFeeElement.innerText = fee.toFixed(2);

                const total = subTotal + fee;
                // set the amount sent to stripe
                const stripeTotal =  fee > 0 ? total.toFixed(2) : subTotal.toFixed(2);
                totalDonationElement.innerText = stripeTotal;
                totalDonationInput.value = stripeTotal;
                totalChurchReceivesElement.innerText = total.toFixed(2);
                submitButton.disabled = total <= 0;
            }
        });
    </script>
</x-app-layout>
