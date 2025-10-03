{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize text-base text-gray-700">{{ $label }}
        <span class="text-gray-500 text-xs">(Optional)</span>
    </legend>
    <input
        type="file"
        {{ $attributes->merge(['class' => 'file-input w-full rounded-xl']) }}
        autofocus
    />
    @error($name)
        <label class="label w-full text-red-500">{{$message}}</label>
    @enderror
</fieldset>
