{{-- Help & Support View --}}
{{-- Prompt 290: Documentation links, FAQs, support ticket submission, contact info --}}

@extends('layouts.app')

@section('title', 'Help & Support')

@section('content')
<div x-data="helpSupport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Help & Support</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Help & Support</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h4 class="text-center mb-3">How can we help you?</h4>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search for help articles, FAQs, tutorials..." 
                               x-model="searchQuery" @input.debounce.300ms="searchHelp()">
                    </div>
                    <div class="text-center mt-3">
                        <span class="text-muted">Popular:</span>
                        <a href="#" class="badge bg-light text-dark text-decoration-none ms-1" @click.prevent="searchQuery = 'student admission'">Student Admission</a>
                        <a href="#" class="badge bg-light text-dark text-decoration-none ms-1" @click.prevent="searchQuery = 'fee payment'">Fee Payment</a>
                        <a href="#" class="badge bg-light text-dark text-decoration-none ms-1" @click.prevent="searchQuery = 'attendance'">Attendance</a>
                        <a href="#" class="badge bg-light text-dark text-decoration-none ms-1" @click.prevent="searchQuery = 'reports'">Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <a href="#documentation" class="card border-0 shadow-sm h-100 text-decoration-none" @click.prevent="activeSection = 'documentation'">
                <div class="card-body text-center py-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-book fs-4 text-primary"></i>
                    </div>
                    <h6 class="mb-0">Documentation</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="#faqs" class="card border-0 shadow-sm h-100 text-decoration-none" @click.prevent="activeSection = 'faqs'">
                <div class="card-body text-center py-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-question-circle fs-4 text-success"></i>
                    </div>
                    <h6 class="mb-0">FAQs</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="#tickets" class="card border-0 shadow-sm h-100 text-decoration-none" @click.prevent="activeSection = 'tickets'">
                <div class="card-body text-center py-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-ticket fs-4 text-warning"></i>
                    </div>
                    <h6 class="mb-0">Support Tickets</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="#contact" class="card border-0 shadow-sm h-100 text-decoration-none" @click.prevent="activeSection = 'contact'">
                <div class="card-body text-center py-4">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-headset fs-4 text-info"></i>
                    </div>
                    <h6 class="mb-0">Contact Us</h6>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Documentation Section -->
            <div class="card border-0 shadow-sm mb-4" x-show="activeSection === 'documentation' || activeSection === 'all'">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-book me-2 text-primary"></i>Documentation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <template x-for="doc in documentation" :key="doc.id">
                            <div class="col-md-6">
                                <a href="#" class="card border h-100 text-decoration-none" @click.prevent="openDoc(doc)">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-light rounded p-2 me-3">
                                                <i class="bi fs-4" :class="doc.icon + ' ' + doc.color"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" x-text="doc.title"></h6>
                                                <p class="text-muted small mb-0" x-text="doc.description"></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- FAQs Section -->
            <div class="card border-0 shadow-sm mb-4" x-show="activeSection === 'faqs' || activeSection === 'all'">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-question-circle me-2 text-success"></i>Frequently Asked Questions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn" :class="faqCategory === 'all' ? 'btn-primary' : 'btn-outline-primary'" 
                                    @click="faqCategory = 'all'">All</button>
                            <button type="button" class="btn" :class="faqCategory === 'general' ? 'btn-primary' : 'btn-outline-primary'" 
                                    @click="faqCategory = 'general'">General</button>
                            <button type="button" class="btn" :class="faqCategory === 'students' ? 'btn-primary' : 'btn-outline-primary'" 
                                    @click="faqCategory = 'students'">Students</button>
                            <button type="button" class="btn" :class="faqCategory === 'fees' ? 'btn-primary' : 'btn-outline-primary'" 
                                    @click="faqCategory = 'fees'">Fees</button>
                            <button type="button" class="btn" :class="faqCategory === 'exams' ? 'btn-primary' : 'btn-outline-primary'" 
                                    @click="faqCategory = 'exams'">Exams</button>
                        </div>
                    </div>
                    <div class="accordion" id="faqAccordion">
                        <template x-for="(faq, index) in filteredFaqs" :key="faq.id">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" 
                                            @click="toggleFaq(faq.id)"
                                            :class="{ 'collapsed': expandedFaq !== faq.id }">
                                        <span x-text="faq.question"></span>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" :class="{ 'show': expandedFaq === faq.id }">
                                    <div class="accordion-body" x-text="faq.answer"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Support Tickets Section -->
            <div class="card border-0 shadow-sm mb-4" x-show="activeSection === 'tickets' || activeSection === 'all'">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-ticket me-2 text-warning"></i>Support Tickets</h5>
                    <button type="button" class="btn btn-primary btn-sm" @click="showNewTicketModal = true">
                        <i class="bi bi-plus-lg me-1"></i> New Ticket
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="ticket in tickets" :key="ticket.id">
                                    <tr>
                                        <td><code x-text="'#' + ticket.id"></code></td>
                                        <td>
                                            <div class="fw-medium" x-text="ticket.subject"></div>
                                            <small class="text-muted" x-text="ticket.category"></small>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getStatusBadgeClass(ticket.status)" x-text="ticket.status"></span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getPriorityBadgeClass(ticket.priority)" x-text="ticket.priority"></span>
                                        </td>
                                        <td x-text="ticket.created_at"></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="viewTicket(ticket)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="tickets.length === 0">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-ticket fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No support tickets</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="card border-0 shadow-sm" x-show="activeSection === 'contact' || activeSection === 'all'">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-headset me-2 text-info"></i>Contact Us</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-envelope fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Email Support</h6>
                                    <p class="text-muted mb-1">For general inquiries and support</p>
                                    <a href="mailto:support@smartschool.com">support@smartschool.com</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-telephone fs-4 text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Phone Support</h6>
                                    <p class="text-muted mb-1">Mon-Fri, 9AM-6PM IST</p>
                                    <a href="tel:+911234567890">+91 123 456 7890</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-chat-dots fs-4 text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Live Chat</h6>
                                    <p class="text-muted mb-1">Available during business hours</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="startLiveChat()">
                                        Start Chat
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Send us a message</h6>
                            <form @submit.prevent="sendMessage()">
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Your Name" x-model="contactForm.name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Your Email" x-model="contactForm.email" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="4" placeholder="Your Message" x-model="contactForm.message" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" :disabled="sendingMessage">
                                    <span x-show="!sendingMessage"><i class="bi bi-send me-1"></i> Send Message</span>
                                    <span x-show="sendingMessage"><span class="spinner-border spinner-border-sm me-1"></span> Sending...</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Video Tutorials -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-play-circle me-2 text-danger"></i>Video Tutorials</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="video in videos" :key="video.id">
                            <a href="#" class="list-group-item list-group-item-action" @click.prevent="playVideo(video)">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded p-2 me-3">
                                        <i class="bi bi-play-fill text-danger"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-medium" x-text="video.title"></div>
                                        <small class="text-muted" x-text="video.duration"></small>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="#" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-youtube me-1"></i> View All Tutorials
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-activity me-2 text-success"></i>System Status</h5>
                </div>
                <div class="card-body">
                    <template x-for="service in systemStatus" :key="service.name">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span x-text="service.name"></span>
                            <span class="badge" :class="service.status === 'Operational' ? 'bg-success' : 'bg-warning'" 
                                  x-text="service.status"></span>
                        </div>
                    </template>
                    <div class="text-center mt-3">
                        <a href="#" class="small text-muted">View Status Page</a>
                    </div>
                </div>
            </div>

            <!-- Useful Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Useful Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-journal-text me-2"></i> User Manual
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-code-slash me-2"></i> API Documentation
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-arrow-repeat me-2"></i> Release Notes
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-shield-check me-2"></i> Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal fade" :class="{ 'show d-block': showNewTicketModal }" tabindex="-1" 
         x-show="showNewTicketModal" @click.self="showNewTicketModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Support Ticket</h5>
                    <button type="button" class="btn-close" @click="showNewTicketModal = false"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitTicket()">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="ticketForm.category" required>
                                    <option value="">Select Category</option>
                                    <option value="Technical Issue">Technical Issue</option>
                                    <option value="Bug Report">Bug Report</option>
                                    <option value="Feature Request">Feature Request</option>
                                    <option value="Account Issue">Account Issue</option>
                                    <option value="Billing">Billing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="ticketForm.priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" x-model="ticketForm.subject" required 
                                       placeholder="Brief description of the issue">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="5" x-model="ticketForm.description" required
                                          placeholder="Please provide detailed information about your issue"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Attachments</label>
                                <input type="file" class="form-control" multiple>
                                <small class="text-muted">Max 5 files, 10MB each. Supported: JPG, PNG, PDF, DOC</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showNewTicketModal = false">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="submitTicket()" :disabled="submittingTicket">
                        <span x-show="!submittingTicket"><i class="bi bi-send me-1"></i> Submit Ticket</span>
                        <span x-show="submittingTicket"><span class="spinner-border spinner-border-sm me-1"></span> Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showNewTicketModal" @click="showNewTicketModal = false"></div>

    <!-- View Ticket Modal -->
    <div class="modal fade" :class="{ 'show d-block': showTicketModal }" tabindex="-1" 
         x-show="showTicketModal" @click.self="showTicketModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ticket <span x-text="'#' + selectedTicket?.id"></span></h5>
                    <button type="button" class="btn-close" @click="showTicketModal = false"></button>
                </div>
                <div class="modal-body" x-show="selectedTicket">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 x-text="selectedTicket?.subject"></h5>
                            <span class="badge" :class="getStatusBadgeClass(selectedTicket?.status)" x-text="selectedTicket?.status"></span>
                            <span class="badge ms-1" :class="getPriorityBadgeClass(selectedTicket?.priority)" x-text="selectedTicket?.priority"></span>
                        </div>
                        <small class="text-muted" x-text="selectedTicket?.created_at"></small>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <p class="mb-0" x-text="selectedTicket?.description || 'No description provided.'"></p>
                    </div>
                    <h6>Responses</h6>
                    <div class="border rounded p-3 bg-light">
                        <p class="text-muted mb-0">No responses yet. Our support team will respond shortly.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showTicketModal = false">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showTicketModal" @click="showTicketModal = false"></div>
</div>
@endsection

@push('scripts')
<script>
function helpSupport() {
    return {
        searchQuery: '',
        activeSection: 'all',
        faqCategory: 'all',
        expandedFaq: null,
        showNewTicketModal: false,
        showTicketModal: false,
        selectedTicket: null,
        submittingTicket: false,
        sendingMessage: false,
        ticketForm: {
            category: '',
            priority: 'Medium',
            subject: '',
            description: ''
        },
        contactForm: {
            name: '',
            email: '',
            message: ''
        },
        documentation: [
            { id: 1, title: 'Getting Started', description: 'Learn the basics of the system', icon: 'bi-rocket-takeoff', color: 'text-primary' },
            { id: 2, title: 'Student Management', description: 'Manage student records and admissions', icon: 'bi-people', color: 'text-success' },
            { id: 3, title: 'Fee Management', description: 'Handle fee collection and invoices', icon: 'bi-currency-dollar', color: 'text-warning' },
            { id: 4, title: 'Exam & Results', description: 'Create exams and manage results', icon: 'bi-journal-check', color: 'text-info' },
            { id: 5, title: 'Attendance System', description: 'Track student and staff attendance', icon: 'bi-calendar-check', color: 'text-danger' },
            { id: 6, title: 'Reports & Analytics', description: 'Generate reports and insights', icon: 'bi-graph-up', color: 'text-secondary' }
        ],
        faqs: [
            { id: 1, category: 'general', question: 'How do I reset my password?', answer: 'You can reset your password by clicking on "Forgot Password" on the login page. Enter your email address and follow the instructions sent to your email.' },
            { id: 2, category: 'general', question: 'How do I change my profile picture?', answer: 'Go to Settings > Profile and click on the profile picture area. You can upload a new image from your computer.' },
            { id: 3, category: 'students', question: 'How do I add a new student?', answer: 'Navigate to Students > Add New Student. Fill in the required information including personal details, guardian information, and class assignment.' },
            { id: 4, category: 'students', question: 'How do I transfer a student to another class?', answer: 'Go to the student\'s profile, click on "Transfer" button, select the new class and section, and confirm the transfer.' },
            { id: 5, category: 'fees', question: 'How do I generate fee invoices?', answer: 'Go to Fees > Generate Invoice. Select the class, fee type, and due date. The system will automatically generate invoices for all students.' },
            { id: 6, category: 'fees', question: 'How do I record a fee payment?', answer: 'Navigate to Fees > Collect Fee. Search for the student, select the pending fees, enter the payment details, and submit.' },
            { id: 7, category: 'exams', question: 'How do I create an exam schedule?', answer: 'Go to Exams > Schedule. Click "Add Exam", select the class, subjects, dates, and times for each exam.' },
            { id: 8, category: 'exams', question: 'How do I enter exam marks?', answer: 'Navigate to Exams > Enter Marks. Select the exam and class, then enter marks for each student. You can also import marks from Excel.' }
        ],
        tickets: [
            { id: 1001, subject: 'Unable to generate report', category: 'Technical Issue', status: 'Open', priority: 'High', created_at: 'Jan 08, 2026', description: 'When I try to generate the monthly attendance report, the system shows an error message.' },
            { id: 1002, subject: 'Feature request: Bulk SMS', category: 'Feature Request', status: 'In Progress', priority: 'Medium', created_at: 'Jan 07, 2026', description: 'Would like to have the ability to send bulk SMS to parents.' },
            { id: 1003, subject: 'Login issue on mobile', category: 'Bug Report', status: 'Resolved', priority: 'Low', created_at: 'Jan 05, 2026', description: 'Cannot login from mobile browser.' }
        ],
        videos: [
            { id: 1, title: 'System Overview', duration: '5:30' },
            { id: 2, title: 'Student Admission Process', duration: '8:45' },
            { id: 3, title: 'Fee Collection Guide', duration: '6:20' },
            { id: 4, title: 'Generating Reports', duration: '4:15' },
            { id: 5, title: 'Attendance Management', duration: '7:00' }
        ],
        systemStatus: [
            { name: 'Application', status: 'Operational' },
            { name: 'Database', status: 'Operational' },
            { name: 'File Storage', status: 'Operational' },
            { name: 'Email Service', status: 'Operational' },
            { name: 'SMS Gateway', status: 'Operational' }
        ],

        get filteredFaqs() {
            if (this.faqCategory === 'all') return this.faqs;
            return this.faqs.filter(f => f.category === this.faqCategory);
        },

        toggleFaq(id) {
            this.expandedFaq = this.expandedFaq === id ? null : id;
        },

        searchHelp() {
            // Implement search functionality
        },

        openDoc(doc) {
            alert('Opening documentation: ' + doc.title);
        },

        playVideo(video) {
            alert('Playing video: ' + video.title);
        },

        getStatusBadgeClass(status) {
            const classes = {
                'Open': 'bg-primary',
                'In Progress': 'bg-warning',
                'Resolved': 'bg-success',
                'Closed': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        getPriorityBadgeClass(priority) {
            const classes = {
                'Low': 'bg-info',
                'Medium': 'bg-warning',
                'High': 'bg-danger',
                'Critical': 'bg-dark'
            };
            return classes[priority] || 'bg-secondary';
        },

        viewTicket(ticket) {
            this.selectedTicket = ticket;
            this.showTicketModal = true;
        },

        submitTicket() {
            if (!this.ticketForm.category || !this.ticketForm.subject || !this.ticketForm.description) {
                alert('Please fill in all required fields');
                return;
            }
            
            this.submittingTicket = true;
            setTimeout(() => {
                this.tickets.unshift({
                    id: 1000 + this.tickets.length + 1,
                    subject: this.ticketForm.subject,
                    category: this.ticketForm.category,
                    status: 'Open',
                    priority: this.ticketForm.priority,
                    created_at: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
                    description: this.ticketForm.description
                });
                this.submittingTicket = false;
                this.showNewTicketModal = false;
                this.ticketForm = { category: '', priority: 'Medium', subject: '', description: '' };
                alert('Ticket submitted successfully!');
            }, 1500);
        },

        sendMessage() {
            if (!this.contactForm.name || !this.contactForm.email || !this.contactForm.message) {
                alert('Please fill in all fields');
                return;
            }
            
            this.sendingMessage = true;
            setTimeout(() => {
                this.sendingMessage = false;
                this.contactForm = { name: '', email: '', message: '' };
                alert('Message sent successfully! We will get back to you soon.');
            }, 1500);
        },

        startLiveChat() {
            alert('Starting live chat...');
        }
    };
}
</script>
@endpush
