<div class="table-wrap">
    <table class="admin-datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Doctor</th>
                <th>Speciality</th>
                <th>City</th>
                <th>Mobile</th>
                <th>Hospital</th>
                <th>Type</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $reel)
                <tr>
                    <td><b>{{ method_exists($items, 'firstItem') ? $items->firstItem() + $loop->index : $loop->iteration }}</b></td>
                    <td>{{ $reel->doctor_name }}</td>
                    <td>{{ $reel->speciality }}</td>
                    <td>{{ $reel->city }}</td>
                    <td>{{ $reel->mobile ?: '—' }}</td>
                    <td>{{ $reel->hospital_name ?: '—' }}</td>
                    <td>{{ ucfirst($reel->content_type ?: 'Not selected') }}</td>
                    <td><span class="status {{ $reel->status }}">{{ str_replace('_', ' ', $reel->status) }}</span></td>
                    <td>{{ $reel->created_at->format('d M Y, h:i A') }}</td>
                    <td><a class="table-action" href="{{ route('admin.doctors.show', $reel) }}">View</a></td>
                </tr>
            @empty
                <tr class="admin-table-empty-row">
                    <td class="empty" colspan="10">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
