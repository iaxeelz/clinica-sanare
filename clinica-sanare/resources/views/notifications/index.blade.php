@extends('vendor.adminlte.layouts.app')

@section('title', 'Notificaciones')
@section('page-title', 'Mis Notificaciones')
@section('breadcrumb')
    <li class="breadcrumb-item active">Notificaciones</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bell"></i> Notificaciones
        </h3>
        <div class="card-tools">
            <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
            </form>
            <form action="{{ route('notifications.destroy-all') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar todas las notificaciones?')">
                    <i class="fas fa-trash"></i> Eliminar todas
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <ul class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <li class="list-group-item {{ $notification->is_read ? 'bg-white' : 'bg-light' }}">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <span class="badge badge-{{ $notification->color }} p-2">
                                    <i class="fas {{ $notification->icon }} fa-lg"></i>
                                </span>
                            </div>
                            <div class="col-md-7">
                                <h5 class="mb-0">{{ $notification->title }}</h5>
                                <p class="mb-0 text-muted">{{ $notification->message }}</p>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> {{ $notification->time_ago }}
                                    @if(!$notification->is_read)
                                        <span class="badge badge-primary ml-2">Nueva</span>
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                @if(!$notification->is_read)
                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-check"></i> Marcar leída
                                        </button>
                                    </form>
                                @endif
                                @if($notification->link)
                                    <a href="{{ $notification->link }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                @endif
                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h4>No hay notificaciones</h4>
                <p class="text-muted">Todas las notificaciones aparecerán aquí.</p>
            </div>
        @endif
    </div>
</div>
@endsection