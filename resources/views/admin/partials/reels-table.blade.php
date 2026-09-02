<div class="table-wrap">
    <table class="admin-datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>City</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $reel)
                <tr>
                    <td><b>{{ method_exists($items, 'firstItem') ? $items->firstItem() + $loop->index : $loop->iteration }}</b></td>
                    <td>{{ $reel->doctor_name }}</td>
                    <td>{{ $reel->city }}</td>
                    <td>{{ ucfirst($filters['media_type'] ?? ($reel->content_type ?: 'Not selected')) }}</td>
                    <td><span class="status {{ $reel->status }}">{{ str_replace('_', ' ', $reel->status) }}</span></td>
                    <td>{{ $reel->created_at->copy()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                    <td>
                        <div class="table-actions">
                            <a class="table-action table-action-view" href="{{ route('admin.doctors.show', [$reel, 'media_type' => $filters['media_type'] ?? $reel->content_type]) }}"><i class="fa-regular fa-eye"></i> View</a>
                            <form method="POST" action="{{ route('admin.doctors.destroy', $reel) }}" data-delete-submission data-name="{{ $reel->doctor_name }}">
                                @csrf
                                @method('DELETE')
                                <button class="table-action table-action-delete" type="submit"><i class="fa-regular fa-trash-can"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="admin-table-empty-row">
                    <td class="empty" colspan="7">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
