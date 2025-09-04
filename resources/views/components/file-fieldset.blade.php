{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    <input
        type="file"
        {{ $attributes->merge(['class' => 'file-input w-full rounded-xl']) }}
        autofocus
    />
    @error($name)
        <label class="label w-full text-red-500">{{$message}}</label>
    @enderror
</fieldset>
