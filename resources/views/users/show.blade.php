@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl mb-3">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ $user->name }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $user])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Imagen de Perfil') }}
                                </h3>

                                <img id="image-preview"
                                     class="img-account-profile mb-2"
                                     src="{{ asset('assets/img/demo/user-placeholder.svg') }}"
                                     alt=""
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    {{ __('Detalles del Usuario') }}
                                </h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                    <tbody>
                                        <tr>
                                            <td>Nombre</td>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Correo electrónico</td>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td>Nombre de usuario</td>
                                            <td>{{ $user->username }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.edit route="{{ route('users.edit', $user) }}">
                                    {{ __('Editar') }}
                                </x-button.edit>

                                <x-button.back route="{{ route('users.index') }}">
                                    {{ __('Cancelar') }}
                                </x-button.back>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
