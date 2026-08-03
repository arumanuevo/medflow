@extends('layouts.modern')

@section('title', 'Detalles del Grupo - MeasureFlow')

@section('content')
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/group-details-styles.css') }}">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-folder btn-icon"></i> {{ $group->name }}</h4>
                    <a href="{{ route('sensor-groups.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver a Grupos
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Grupo</h5>
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8">{{ $group->id }}</dd>

                                <dt class="col-sm-4">Descripción:</dt>
                                <dd class="col-sm-8">{{ $group->description ?? 'Sin descripción' }}</dd>

                                <dt class="col-sm-4">Fecha de creación:</dt>
                                <dd class="col-sm-8">{{ $group->created_at->format('d/m/Y H:i') }}</dd>

                                <dt class="col-sm-4">Plantilla:</dt>
                                <dd class="col-sm-8">
                                    @if($group->template)
                                        {{ $group->template->name }} ({{ $group->template->type }})
                                    @else
                                        Sin plantilla asignada
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h5>Acciones</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('sensor-groups.edit', $group->id) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Editar Grupo
                                </a>
                                <a href="{{ route('sensors.create') }}?group_id={{ $group->id }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Agregar Sensor
                                </a>
                                <form action="{{ route('sensor-groups.destroy', $group->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este grupo?')">
                                        <i class="bi bi-trash"></i> Eliminar Grupo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Sensores en este Grupo ({{ $group->sensors->count() }})</h5>
                        @if($group->sensors->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Identificador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group->sensors as $sensor)
                                    <tr>
                                        <td>{{ $sensor->id }}</td>
                                        <td>{{ $sensor->name }}</td>
                                        <td>{{ $sensor->identifier }}</td>
                                        <td>
                                        <a href="/sensors/{{ $sensor->id }}" class="btn btn-sm btn-info">
    <i class="bi bi-eye"></i> Ver
</a>
</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Este grupo no tiene sensores asignados.
                            <a href="{{ route('sensors.create') }}?group_id={{ $group->id }}">Agregar uno ahora</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection