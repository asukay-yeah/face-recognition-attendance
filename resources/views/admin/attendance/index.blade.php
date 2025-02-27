@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Attendance Reports') }}</span>
                    <div>
                        <form action="{{ route('admin.attendance') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                            </div>
                            <div class="form-group mr-2">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            <a href="{{ route('admin.attendance') }}" class="btn btn-sm btn-secondary ml-1">Reset</a>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Working Hours</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $attendance->user->id) }}">
                                                {{ $attendance->user->name }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}</td>
                                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}</td>
                                        <td>
                                            @if ($attendance->check_in && $attendance->check_out)
                                                @php
                                                    $checkIn = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->check_in);
                                                    $checkOut = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->check_out);
                                                    $hours = $checkOut->diffInHours($checkIn);
                                                    $minutes = $checkOut->copy()->subHours($hours)->diffInMinutes($checkIn);
                                                @endphp
                                                {{ $hours }}h {{ $minutes }}m
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if ($attendance->status === 'present')
                                                <span class="badge badge-success">Present</span>
                                            @elseif ($attendance->status === 'late')
                                                <span class="badge badge-warning">Late</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($attendance->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal{{ $attendance->id }}">
                                                    Edit
                                                </button>
                                                <form action="{{ route('admin.attendance.destroy', $attendance->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
                                                </form>
                                            </div>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal{{ $attendance->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $attendance->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editModalLabel{{ $attendance->id }}">Edit Attendance</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="date">Date</label>
                                                                    <input type="date" class="form-control" id="date" name="date" value="{{ $attendance->date }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="check_in">Check-in Time</label>
                                                                    <input type="time" class="form-control" id="check_in" name="check_in" value="{{ $attendance->check_in }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="check_out">Check-out Time</label>
                                                                    <input type="time" class="form-control" id="check_out" name="check_out" value="{{ $attendance->check_out }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="status">Status</label>
                                                                    <select class="form-control" id="status" name="status" required>
                                                                        <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>Present</option>
                                                                        <option value="late" {{ $attendance->status === 'late' ? 'selected' : '' }}>Late</option>
                                                                        <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="notes">Notes</label>
                                                                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ $attendance->notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $attendances->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection