{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full">{{ $label }}</legend>
    <input type="file" wire:model="{{ $name }}" id="{{ $name }}" class="file-input w-full" value="{{ old($name) }}" required autofocus autocomplete="{{ $name }}" />
    @error($name)
        <label class="label w-full">{{$message}}</label>
    @enderror
</fieldset>
