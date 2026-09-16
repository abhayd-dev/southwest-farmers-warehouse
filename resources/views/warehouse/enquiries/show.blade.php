<x-app-layout title="Enquiry Details">
    <div class="container-fluid">
        <div class="py-4 d-flex align-items-center gap-3">
            <a href="{{ route('warehouse.enquiries.index') }}" class="btn btn-light border rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="mdi mdi-arrow-left fs-4"></i>
            </a>
            <h4 class="fw-bold m-0 text-dark">Enquiry Details</h4>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="mdi mdi-email-open-outline me-2 text-primary"></i>Message</h6>
                        <span class="text-muted small">{{ $enquiry->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="card-body p-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Subject</label>
                        <h5 class="fw-bold text-dark mb-4">{{ $enquiry->subject }}</h5>
                        <label class="form-label fw-bold text-muted small text-uppercase">Message</label>
                        <div class="bg-light rounded-3 p-3" style="white-space: pre-wrap;">{{ $enquiry->message }}</div>
                    </div>
                </div>

                @if($enquiry->isResolved())
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-success"><i class="mdi mdi-check-circle me-1"></i> Resolved</h6>
                            <p class="text-muted mb-0">{{ optional($enquiry->resolved_at)->format('d M Y, h:i A') }}</p>
                            @if($enquiry->resolution_notes)
                                <div class="mt-2 pt-2 border-top">{{ $enquiry->resolution_notes }}</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Mark Resolved</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('warehouse.enquiries.resolve', $enquiry->id) }}" method="POST">
                                @csrf
                                <textarea name="resolution_notes" class="form-control mb-3" rows="3" placeholder="How was this resolved? (optional)"></textarea>
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check-circle me-1"></i> Mark as Resolved
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="mdi mdi-account-circle-outline me-2 text-info"></i>Sender</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Name</label>
                            <div class="fw-bold">{{ $enquiry->name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Email</label>
                            <div><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></div>
                        </div>
                        <div>
                            <label class="form-label fw-bold text-muted small text-uppercase">Stage</label>
                            <div>
                                @if($enquiry->isResolved())
                                    <span class="badge bg-secondary">Resolved</span>
                                @elseif($enquiry->isEscalatedToAdmin())
                                    <span class="badge bg-danger">Super Admin</span>
                                @else
                                    <span class="badge bg-warning">Warehouse</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($enquiry->isEscalatedToWarehouse() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_enquiries')))
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <p class="text-muted small mb-3">Needs the Super Admin's attention specifically? Escalate it further.</p>
                            <form action="{{ route('warehouse.enquiries.escalate-admin', $enquiry->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i> Escalate to Super Admin
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
