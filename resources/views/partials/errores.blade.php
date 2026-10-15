@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <h2 class="h6">Revisa los datos introducidos</h2>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
