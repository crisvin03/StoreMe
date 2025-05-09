<!-- resources/views/auth/login.blade.php -->

<x-guest-layout>
    <h2 class="text-center text-2xl font-bold mb-6">Welcome to StoreMe!</h2>

    <!-- Success Message -->
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 text-center">
            {{ session('status') }}
        </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 text-center">
            {{ session('error') }}
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600 text-center">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4" onsubmit="return validateTermsAgreement()">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm">Enter your username</label>
            <input id="email" class="mt-1 block w-full rounded-md bg-blue-300 text-white border-none placeholder-white focus:ring-2 focus:ring-blue-300"
                   type="email" name="email" :value="old('email')" required autofocus placeholder="Email" />
        </div>

        <!-- Password with visibility toggle -->
<div>
    <label for="password" class="block text-sm">Enter your password</label>
    <div class="relative">
        <input 
            id="password" 
            class="mt-1 block w-full rounded-md bg-blue-300 text-white border-none placeholder-white focus:ring-2 focus:ring-blue-300 pr-10" 
            type="password" 
            name="password" 
            required 
            placeholder="Password"
        />
        <!-- Button that swaps icon -->
        <button 
            type="button" 
            onclick="togglePassword()" 
            class="absolute inset-y-0 right-3 flex items-center text-white focus:outline-none"
        >
            <!-- Heroicon Eye (default: visible) -->
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path id="eyePath2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                         4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>
    </div>
</div>
 <!-- Terms and Conditions Checkbox -->
 <div class="flex items-start gap-2 mt-4">
    <input 
        id="terms" 
        name="terms" 
        type="checkbox" 
        onclick="handleTermsClick(event)" 
        class="rounded text-blue-600 focus:ring-blue-500"
    >
    <label for="terms" class="text-sm text-white-700 select-none">
        I agree to the Terms and Conditions.
    </label>
</div>

        <div class="flex flex-col gap-3 mt-6">
            <button class="w-full py-2 rounded-md bg-blue-600 hover:bg-blue-700 font-semibold text-white">Log in</button>

            @if (Route::has('password.request'))
                <a class="block w-full text-center py-2 rounded-md bg-blue-500 hover:bg-blue-600 font-semibold text-white"
                   href="{{ route('password.request') }}">
                    Forgot Password
                </a>
            @endif

            <a class="block w-full text-center py-2 rounded-md bg-blue-500 hover:bg-blue-600 font-semibold text-white"
               href="{{ route('register') }}">
                Sign up here
            </a>
        </div>
        </form>

<!-- Terms and Conditions Modal -->
<div id="termsModal" class="fixed inset-0 hidden bg-black bg-opacity-80 flex justify-center items-center z-50 p-4 transition-all duration-300 opacity-0">
    <div class="bg-blue-700 p-8 rounded-2xl max-w-2xl w-full relative shadow-2xl transform scale-95 transition-all duration-300">
        <h2 class="text-3xl font-bold mb-6 text-center text-white">Terms and Conditions</h2>

        <div class="h-80 overflow-y-auto text-base text-white leading-relaxed p-2 space-y-4 font-semibold">
            <p>Welcome to StoreMe! These terms and conditions govern your use of our locker rental services, including the website and any associated services. By using our Services, you agree to be bound by these Terms.</p>

            <ul class="list-disc list-inside pl-4 space-y-2">
                <li>You will have access to the rented locker during the rental period specified in your reservation.</li>
                <li>Rental fees are due in full at the time of reservation. Payments are non-refundable unless otherwise stated.</li>
                <li>A security payment may be required to cover any damages or additional fees.</li>
                <li>Exceeding the rental period will result in additional charges.</li>
                <li>Do not store illegal, hazardous, or perishable items.</li>
                <li>StoreMe reserves the right to inspect lockers and terminate access if terms are violated.</li>
                <li>Losses are only covered under specific conditions and exclude technological items.</li>
            </ul>

            <p>By using the website and renting a locker, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
        </div>

        <!-- Accept and Cancel Buttons -->
        <div class="flex justify-center gap-6 mt-8">
            <button onclick="acceptTerms()" class="bg-white text-blue-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-full text-lg transition-all duration-300">
                Accept
            </button>
        </div>
    </div>
</div>


<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
              let termsAccepted = false; // Track if terms were accepted already
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <!-- Eye with slash (unsee) from Heroicons outline -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M13.875 18.825A10.05 10.05 0 0112 
                19c-4.478 0-8.268-2.943-9.542-7a9.964 
                9.964 0 012.293-3.95M6.62 6.62A9.969 
                9.969 0 0112 5c4.478 0 8.268 2.943 
                9.542 7a9.953 9.953 0 01-4.276 
                5.114M15 12a3 3 0 01-3 3m0 
                0a3 3 0 01-3-3m3 3l5 5m-5-5l-5 5"/>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <!-- Regular eye icon -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.522 5 12 
                5c4.478 0 8.268 2.943 9.542 7-1.274 
                4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z"/>
        `;
    }
}
function handleTermsClick(event) {
        const checkbox = document.getElementById('terms');

        if (!termsAccepted) {
            event.preventDefault(); // Stop the checkbox from toggling
            openTermsModal();
        } else {
            // After accepting, just toggle normally
            termsAccepted = checkbox.checked; // Update the tracker (true or false)
        }
    }

    function openTermsModal() {
        const modal = document.getElementById('termsModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100', 'scale-100');
            modal.classList.remove('opacity-0', 'scale-95');
        }, 10);
    }

    function acceptTerms() {
        const modal = document.getElementById('termsModal');
        modal.classList.remove('opacity-100', 'scale-100');
        modal.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('terms').checked = true;
            termsAccepted = true; // Mark as accepted
        }, 300);
    }

    function validateTermsAgreement() {
    const checkbox = document.getElementById('terms');
    if (!checkbox.checked) {
        Swal.fire({
            icon: 'warning',
            title: 'Terms and Conditions Required',
            text: 'Please accept the Terms and Conditions before logging in.',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Okay'
        });
        return false; // Stop form submission
    }
    return true; // Allow form submission
}
</script>
    </form>
</x-guest-layout>
