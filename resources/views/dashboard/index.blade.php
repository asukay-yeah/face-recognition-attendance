@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4 class="mb-4">Welcome, {{ $user->name }}</h4>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    {{ __('Face Registration Status') }}
                                </div>
                                <div class="card-body">
                                @if ($hasFaceRegistered)
                                    <div class="alert alert-success">
                                        <i class="fa fa-check-circle"></i> Your face is registered in the system.
                                    </div>
                                    @if(Auth::user()->role === 'admin')
                                    <p>As an admin, you can register and manage faces for users from the <a href="{{ route('admin.users') }}">Users Management</a> page.</p>
                                    @else
                                    <p>Your face has been registered by an administrator. Contact them if you need updates.</p>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i> Your face is not registered yet.
                                    </div>
                                    @if(Auth::user()->role === 'admin')
                                    <p>As an admin, you can register your own face from the <a href="{{ route('admin.users') }}">Users Management</a> page.</p>
                                    @else
                                    <p>Please contact an administrator to register your face for the attendance system.</p>
                                    @endif
                                @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    {{ __('Today\'s Attendance') }}
                                </div>
                                <div class="card-body">
                                    @if ($todayAttendance)
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Date</th>
                                                <td>{{ \Carbon\Carbon::parse($todayAttendance->date)->format('M d, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Check-in</th>
                                                <td>{{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Check-out</th>
                                                <td>{{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') : 'Not checked out yet' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    @if ($todayAttendance->status === 'present')
                                                        <span class="badge badge-success">Present</span>
                                                    @elseif ($todayAttendance->status === 'late')
                                                        <span class="badge badge-warning">Late</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ ucfirst($todayAttendance->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        @if (!$todayAttendance->check_out)
                                            <a href="{{ route('kiosk.index') }}" class="btn btn-primary">Check-out</a>
                                        @endif
                                    @else
                                        <p>You haven't marked your attendance today.</p>
                                        <a href="{{ route('kiosk.index') }}" class="btn btn-primary">Mark Attendance</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            {{ __('Recent Attendance History') }}
                        </div>
                        <div class="card-body">
                            @if (count($recentAttendances) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentAttendances as $attendance)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                                    <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}</td>
                                                    <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}</td>
                                                    <td>
                                                        @if ($attendance->status === 'present')
                                                            <span class="badge badge-success">Present</span>
                                                        @elseif ($attendance->status === 'late')
                                                            <span class="badge badge-warning">Late</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($attendance->status) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <a href="{{ route('attendance.history') }}" class="btn btn-outline-secondary">View Full History</a>
                            @else
                                <p>No attendance records found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection