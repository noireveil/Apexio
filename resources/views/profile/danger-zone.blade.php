@component('profile.layout')
    <h4 class="fw-bold text-danger mb-4">Danger Zone</h4>
    
    <div class="card border-danger shadow-sm">
        <div class="card-body p-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endcomponent