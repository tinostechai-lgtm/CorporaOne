<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Your Plan</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('asset/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/custom.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            opacity: 0.1;
            z-index: -1;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        h1 {
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 700;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card.bg-dark {
            background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%) !important;
        }
        
        .card-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .card-subtitle {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .card-subtitle span {
            font-size: 1rem;
            color: inherit;
            opacity: 0.8;
        }
        
        .card ul li {
            padding: 0.25rem 0;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .card ul li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4facfe;
            font-weight: bold;
        }
        
        .card.bg-dark ul li:before {
            color: #00f2fe;
        }
        
        .btn {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary {
            border-width: 2px;
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 1rem;
            }
            
            .row {
                flex-direction: column;
            }
            
            .col-lg-4 {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Choose Your Perfect Plan</h1>

        <!-- Plans Modal -->
        <div class="modal show d-block" id="plansModal" tabindex="-1" aria-labelledby="plansModalLabel" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="plansModalLabel">Select a Plan That Fits Your Needs</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @php
                                $admin_payment_setting = Utility::getAdminPaymentSetting();
                            @endphp
                            @foreach($plans as $key => $plan)
                                @if($plan->is_disable == 1)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100 @if($key == 1) bg-dark text-white @else bg-white @endif">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title @if($key == 1) text-warning @else text-primary @endif">{{ $plan->name }}</h5>
                                            <h6 class="card-subtitle mb-2">
                                                {{ $admin_payment_setting['currency_symbol'] ?? '$' }}{{ intval($plan->price) }}
                                                <span>/{{ $plan->duration }}</span>
                                            </h6>
                                            <ul class="list-unstyled mt-3 mb-4">
                                                <li>{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }} Users</li>
                                                <li>{{ $plan->max_customers == -1 ? 'Unlimited' : $plan->max_customers }} Customers</li>
                                                <li>{{ $plan->max_venders == -1 ? 'Unlimited' : $plan->max_venders }} Vendors</li>
                                                <li>{{ $plan->max_clients == -1 ? 'Unlimited' : $plan->max_clients }} Clients</li>
                                                <li>Account: {{ $plan->account == 1 ? 'Enabled' : 'Disabled' }}</li>
                                                <li>CRM: {{ $plan->crm == 1 ? 'Enabled' : 'Disabled' }}</li>
                                                <li>HRM: {{ $plan->hrm == 1 ? 'Enabled' : 'Disabled' }}</li>
                                                <li>Project: {{ $plan->project == 1 ? 'Enabled' : 'Disabled' }}</li>
                                                <li>POS: {{ $plan->pos == 1 ? 'Enabled' : 'Disabled' }}</li>
                                                <li>ChatGPT: {{ $plan->chatgpt == 1 ? 'Enabled' : 'Disabled' }}</li>
                                            </ul>
                                            <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}" class="btn @if($key == 1) btn-warning @else btn-outline-primary @endif mt-auto">
                                                Get Started
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('asset/js/vendors/bootstrap.bundle.min.js') }}"></script>
</body>

</html>