<div class="mb-3">
    <label for="slug" class="form-label required">
        {{ __('URL amigable') }}
    </label>

    <input type="text"
           id="slug"
           name="slug"
           wire:model.blur="slug"
           placeholder="Ingresa una URL amigable"
           class="form-control @error('slug') is-invalid @enderror"
    />

    @error('slug')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
