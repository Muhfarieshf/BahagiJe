<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Login - BahagiJe');
?>

<div class="min-h-screen flex flex-col lg:flex-row bg-slate-50 dark:bg-slate-900 transition-colors relative overflow-hidden">
    
    <!-- Left Side: Hero/Pitch -->
    <div class="flex-1 p-8 sm:p-12 lg:p-16 flex flex-col justify-center relative overflow-hidden bg-blue-600 dark:bg-slate-800 text-white shadow-2xl z-20">
        <!-- Background Decorative Elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-700 dark:from-slate-800 dark:to-slate-900 opacity-90 z-0"></div>
        <div class="absolute top-[-20%] left-[-10%] w-[40rem] h-[40rem] bg-white/5 rounded-full blur-3xl pointer-events-none z-0"></div>
        <div class="absolute bottom-[-10%] right-[-20%] w-[30rem] h-[30rem] bg-indigo-400/20 rounded-full blur-2xl pointer-events-none z-0"></div>
        
        <div class="relative z-10 max-w-xl mx-auto lg:mx-0">
            <!-- Mobile Logo (Hidden on Desktop) -->
            <div class="flex justify-center lg:hidden mb-8">
                <img src="<?= $this->Url->image('bahagije.png') ?>" alt="BahagiJe Logo" class="h-24 w-auto drop-shadow-lg filter brightness-0 invert">
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight text-center lg:text-left">
                Split expenses without the math headache.
            </h1>
            <p class="text-lg sm:text-xl text-blue-100 dark:text-slate-300 mb-10 text-center lg:text-left font-medium">
                BahagiJe handles complex calculations like SST, service charges, and uneven splits so you don't have to.
            </p>

            <div class="space-y-8 hidden sm:block">
                <!-- Step 1 -->
                <div class="flex items-start gap-5">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-white/20 shadow-inner flex items-center justify-center font-bold text-xl backdrop-blur-sm border border-white/30">1</div>
                    <div>
                        <h3 class="text-xl font-bold tracking-wide">Pick a Scenario</h3>
                        <p class="text-blue-100 dark:text-slate-300 text-base mt-1.5 leading-relaxed">Select from presets like Dinners, Road Trips, or Groceries, and we'll configure the math.</p>
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="flex items-start gap-5">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-white/20 shadow-inner flex items-center justify-center font-bold text-xl backdrop-blur-sm border border-white/30">2</div>
                    <div>
                        <h3 class="text-xl font-bold tracking-wide">Share the Link</h3>
                        <p class="text-blue-100 dark:text-slate-300 text-base mt-1.5 leading-relaxed">Guests can join your session instantly via a link or code—no app download required.</p>
                    </div>
                </div>
                <!-- Step 3 -->
                <div class="flex items-start gap-5">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-white/20 shadow-inner flex items-center justify-center font-bold text-xl backdrop-blur-sm border border-white/30">3</div>
                    <div>
                        <h3 class="text-xl font-bold tracking-wide">Add Expenses</h3>
                        <p class="text-blue-100 dark:text-slate-300 text-base mt-1.5 leading-relaxed">Enter receipt items. We'll automatically calculate exactly who owes what.</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-wrap justify-center lg:justify-start gap-3">
                <span class="px-4 py-2 rounded-full bg-white/10 shadow-sm text-sm font-semibold border border-white/20 backdrop-blur-md text-white transition hover:bg-white/20 cursor-default">🍽️ Dinners</span>
                <span class="px-4 py-2 rounded-full bg-white/10 shadow-sm text-sm font-semibold border border-white/20 backdrop-blur-md text-white transition hover:bg-white/20 cursor-default">🚗 Road Trips</span>
                <span class="px-4 py-2 rounded-full bg-white/10 shadow-sm text-sm font-semibold border border-white/20 backdrop-blur-md text-white transition hover:bg-white/20 cursor-default">🛒 Groceries</span>
                <span class="px-4 py-2 rounded-full bg-white/10 shadow-sm text-sm font-semibold border border-white/20 backdrop-blur-md text-white transition hover:bg-white/20 cursor-default">✈️ Vacations</span>
            </div>
        </div>
    </div>

    <!-- Right Side: Action (Login Box) -->
    <div class="lg:w-[500px] xl:w-[600px] flex items-center justify-center p-8 lg:p-12 relative z-10 bg-slate-50 dark:bg-slate-900">
        <!-- Subtle background shapes for the right side -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-400/10 dark:bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[30rem] h-[30rem] bg-indigo-400/10 dark:bg-indigo-600/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="max-w-md w-full space-y-8 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 relative z-10 transition-all">
            
            <div class="hidden lg:block">
                <div class="flex justify-center transform transition duration-500 hover:scale-105">
                    <img src="<?= $this->Url->image('bahagije.png') ?>" alt="BahagiJe Logo" class="h-20 w-auto drop-shadow-sm">
                </div>
                <p class="mt-4 text-center text-sm font-semibold text-slate-500 dark:text-slate-400 tracking-wide uppercase">
                    Get Started
                </p>
            </div>
            
            <div class="lg:hidden text-center">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Get Started</h2>
            </div>
            
            <!-- Display any flash messages (errors/success) -->
            <div class="mt-2 empty:hidden">
                <?= $this->Flash->render() ?>
            </div>

            <div class="mt-8">
                <a href="<?= $this->Url->build(['controller' => 'Auth', 'action' => 'google']) ?>"
                   class="group relative w-full flex justify-center items-center gap-3 py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-slate-700 dark:text-white bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 shadow-sm hover:shadow-md border-slate-200 dark:border-slate-600 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:focus:ring-slate-700 transition-all duration-300 transform hover:-translate-y-0.5">
                    <span class="flex items-center">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            <path fill="none" d="M0 0h48v48H0z"/>
                        </svg>
                    </span>
                    Sign in with Google
                </a>
            </div>

            <div class="relative flex items-center py-6">
                <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                <span class="flex-shrink-0 mx-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Or</span>
                <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 text-left">Continue as Guest</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" id="joinCodeInput" placeholder="Invite Link or Session Code" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50 outline-none text-sm bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all"
                           onkeypress="if(event.key === 'Enter') processJoinCode()">
                    <button type="button" onclick="processJoinCode()" 
                            class="w-full sm:w-auto text-center justify-center px-6 py-3 bg-slate-800 dark:bg-blue-600 text-white font-bold rounded-xl hover:bg-slate-900 dark:hover:bg-blue-500 transition-colors shadow-sm focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-blue-900/30 whitespace-nowrap">
                        Join ➔
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2.5 text-left leading-relaxed">Enter the 6-character code or paste the link shared by the host to join without an account.</p>
            </div>
            
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700/50 text-xs font-medium text-slate-400 dark:text-slate-500 text-center lg:text-left">
                By signing in, you agree to our <a href="<?= $this->Url->build('/terms') ?>" class="text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Terms of Service</a> and <a href="<?= $this->Url->build('/privacy') ?>" class="text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Privacy Policy</a>.
            </div>
        </div>
    </div>
</div>

<script>
function processJoinCode() {
    const input = document.getElementById('joinCodeInput').value.trim();
    if (!input) return;
    let code = input;
    try {
        const url = new URL(input);
        const parts = url.pathname.split('/').filter(Boolean);
        code = parts[parts.length - 1];
    } catch (e) {}
    window.location.href = "<?= $this->Url->build('/sessions/join/') ?>" + encodeURIComponent(code);
}
</script>
