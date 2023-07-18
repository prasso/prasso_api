
<form wire:submit.prevent="submit">
    @csrf
  
<div class="p-4 bg-white rounded shadow" >
        @if (session()->has('message'))
        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
            <div>
                <p class="m-auto text-center text-sm">{{ session('message') }}</p>
            </div>
        </div>
        @endif
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
        @endif
    <input type="hidden" id="id" wire:model="template.id">

    <div class="mb-4">
        <label for="templatename" class="block font-medium text-gray-700">Template Name:</label>
        <input type="text" id="templatename" x-bind:value="template.templatename" wire:model="template.templatename" 
        class="form-input mt-1 block w-full rounded-md shadow-sm">
    </div>


    <div class="mb-4">
        <label for="title" class="block font-medium text-gray-700">Title:</label>
        <input type="text" id="title" wire:model="template.title" class="form-input mt-1 block w-full rounded-md shadow-sm">
    </div>

    <div class="mb-4">
        <label for="description" class="block font-medium text-gray-700">Description:</label>
        <textarea id="description" wire:model="template.description" class="form-textarea mt-1 block w-full rounded-md shadow-sm"></textarea>
    </div>

    <div class="mb-4">
        <label for="template_data_model" class="block font-medium text-gray-700">Template Data Model:</label>
        <input type="text" id="template_data_model" wire:model="template.template_data_model" class="form-input mt-1 block w-full rounded-md shadow-sm">
    </div>

    <div class="mb-4">
        <label for="template_where_clause" class="block font-medium text-gray-700">Template Where Clause:</label>
        <input type="text" id="template_where_clause" wire:model="template.template_where_clause" class="form-input mt-1 block w-full rounded-md shadow-sm">
    </div>

    <div class="mb-4">
        <label for="template_data_query" class="block font-medium text-gray-700">Template Data Query:</label>
        <textarea id="template_data_query" wire:model="template.template_data_query" class="form-textarea mt-1 block w-full rounded-md shadow-sm"></textarea>
    </div>

    <div class="mb-4">
        <label for="order_by_clause" class="block font-medium text-gray-700">Order By Clause:</label>
        <input type="text" id="order_by_clause" wire:model="template.order_by_clause" class="form-input mt-1 block w-full rounded-md shadow-sm">
    </div>

    <div class="mb-4">
        <label for="include_csrf" class="inline-flex items-center">
            <input type="checkbox" id="include_csrf" wire:model="template.include_csrf" class="form-checkbox">
            <span class="ml-2 text-gray-700">This is a single item form that will be saved</span>
        </label>
    </div>
    <div class="mb-4">
        <label for="default_blank" class="inline-flex items-center">
            <span class="ml-2 text-gray-700">Json for default blank form</span>
        </label>
            <textarea id="default_blank" wire:model="template.default_blank" class="form-textarea mt-1 block w-full rounded-md shadow-sm"></textarea>
            
    </div>
</div>
    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
            <button wire:click.prevent="submit()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
            Save
            </button>
        </span>
        <span class="gray-200 flex mr-10 text-sm shadow-sm sm:mt-0 sm:w-auto">
            <a class="mt-3 text-gray-700 " href="javascript:window.history.back()" @click="history.back()">Cancel</a>
        </span>
    </div>
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
        @endif
</form>