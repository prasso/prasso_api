<?php
// a form to be used by A GoGo Delivery representative to information about their delivery
// items, including the delivery location, recipient, potential delivery dates, 
// and any relevant details.
?>

<form>
  <div class="flex flex-wrap">
    <div class="w-1/3 pr-2">
      <label class="text-lg mb-2">House Number</label>
      <input class="bg-gray-200 p-2 rounded-md" type="text" name="house_number" required>
    </div>
    <div class="w-1/3 pr-2">
      <label class="text-lg mb-2">Street Name</label>
      <input class="bg-gray-200 p-2 rounded-md" type="text" name="street_name" required>
    </div>
    <div class="w-1/3">
      <label class="text-lg mb-2">City</label>
      <input class="bg-gray-200 p-2 rounded-md" type="text" name="city" required>
    </div>
    <div class="w-1/3 pr-2 mt-4">
      <label class="text-lg mb-2">State</label>
      <input class="bg-gray-200 p-2 rounded-md" type="text" name="state" required>
    </div>
    <div class="w-1/3 pr-2 mt-4">
      <label class="text-lg mb-2">Zipcode</label>
      <input class="bg-gray-200 p-2 rounded-md" type="text" name="zipcode" required>
    </div>
  </div>
  <div class="flex flex-col mt-4">
    <label class="text-lg mb-2">Recipient Name</label>
    <input class="bg-gray-200 p-2 rounded-md" type="text" name="recipient" required>
  </div>
  <div class="flex flex-col mt-4">
    <label class="text-lg mb-2">Potential Delivery Dates</label>
    <input type="date" class="bg-gray-200 p-2 rounded-md" name="delivery_dates" required>
  </div>
  <div class="flex flex-col mt-4">
    <label class="text-lg mb-2">Details</label>
    <textarea rows="5" class="bg-gray-200 p-2 rounded-md" name="details"></textarea>
  </div>
  <button class="bg-green-500 text-white p-2 rounded-md mt-4">Submit</button>
</form>

