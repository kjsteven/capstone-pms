
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <style>@import url(https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css);</style>



</head>
<body>


        <div class="bg-gray-50 font-[sans-serif]">
            <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4" x-data="app()">
                <div class="max-w-md w-full">
                <!-- <a href="javascript:void(0)"><img
                    src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-40 mb-8 mx-auto block' />
                </a> -->

                <div class="p-8 rounded-2xl bg-white shadow-md">
                    <h2 class="text-gray-800 text-center text-2xl font-bold">Sign up</h2>
                    <form method="POST" class="mt-8 space-y-4">

                    <div>
                        <label class="text-gray-800 text-sm mb-2 block">Name</label>
                        <div class="relative flex items-center">
                        <input name="name" type="name" id="name" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter name" />
                        </div>
                    </div>


                    <div>
                        <label class="text-gray-800 text-sm mb-2 block">Email</label>
                        <div class="relative flex items-center">
                        <input name="email" type="email" id="email" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter email" />
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.7-8 5.334L4 8.7V6.297l8 5.333 8-5.333V8.7z"></path></svg>
                        </div>
                    </div>


                    <div>
                        <label class="text-gray-800 text-sm mb-2 block">Username</label>
                        <div class="relative flex items-center">
                        <input name="username" type="text" id="username" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter username" />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4" viewBox="0 0 24 24">
                            <circle cx="10" cy="7" r="6" data-original="#000000"></circle>
                            <path d="M14 15H6a5 5 0 0 0-5 5 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 5 5 0 0 0-5-5zm8-4h-2.59l.3-.29a1 1 0 0 0-1.42-1.42l-2 2a1 1 0 0 0 0 1.42l2 2a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42l-.3-.29H22a1 1 0 0 0 0-2z" data-original="#000000"></path>
                        </svg>
                        </div>
                    </div>

                    <div>
                        <label class="text-gray-800 text-sm mb-2 block">Password</label>
                        <div class="relative flex items-center">
                        <input type="password" id="password" x-model="password" name="password" @input="checkStrength" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                            <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" class="absolute inset-y-0 right-4 flex items-center">
                                <i id="togglePasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex -mx-1 mt-2">
                        <template x-for="(v,i) in 5" :key="i">
                            <div class="w-1/5 px-1">
                                <div class="h-2 rounded-xl transition-colors"
                                    :class="i < passwordScore ? (passwordScore === 5 ? 'bg-green-500' : 'bg-red-400') : 'bg-gray-200'">
                                </div>
                            </div>
                        </template>
                    </div>

                  
                  
                    <div>
                        <label class="text-gray-800 text-sm mb-2 block">Confirm Password</label>
                        <div class="relative flex items-center">
                            <input id="confirmPassword" name="confirmPassword" type="password" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                            <button type="button" onclick="togglePassword('confirmPassword', 'toggleConfirmPasswordIcon')" class="absolute inset-y-0 right-4 flex items-center">
                                <i id="toggleConfirmPasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                            </button>
                        </div>
                    </div>

                   


                    

                    <div class="flex">
                        <input type="checkbox" x-model="termsAccepted" class="w-4" />
                        <label class="text-sm ml-2 text-gray-500">I have read and accept the <a href="javascript:void(0)"
                            class="text-sm text-blue-600 hover:underline">Terms and Conditions</a></label>
                    </div>

                    <div class="!mt-8">
                        <button type="submit" :disabled="!isFormValid" class="w-full py-3 px-4 text-sm tracking-wide rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                            Sign up
                        </button>
                    </div>

                    <p class="text-gray-800 text-sm !mt-8 text-center">Already have an account? <a href="login.php" class="text-blue-600 hover:underline ml-1 whitespace-nowrap font-semibold">Login here</a></p>
                    
                </form>
                </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="text-red-500 text-sm mt-8"><?php echo $error; ?></div>
                <?php endif; ?>
                
            </div>
        </div>




    
</body>


<script defer>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("bxs-show");
                toggleIcon.classList.add("bxs-hide");
             
             
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("bxs-hide");
                toggleIcon.classList.add("bxs-show");
                
              
            }
        }
</script>

<script>
function app() {
    return {
        showPasswordField: true,
        passwordScore: 0,
        password: '',
        chars: {
            lower: 'abcdefghijklmnopqrstuvwxyz',
            upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            numeric: '0123456789',
            symbols: '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~'
        },
        charsLength: 12,
        checkStrength: function() { 
            if (!this.password) {
                this.passwordScore = 0;
                return;
            }
            
            let score = 0;

            // Check for minimum length
            if (this.password.length >= 8) {
                score++;
            }

            // Check for lowercase letters
            if (/[a-z]/.test(this.password)) {
                score++;
            }

            // Check for uppercase letters
            if (/[A-Z]/.test(this.password)) {
                score++;
            }

            // Check for numbers
            if (/\d/.test(this.password)) {
                score++;
            }

            // Check for special characters
            if (/[!@#$%^&*]/.test(this.password)) {
                score++;
            }

            this.passwordScore = score;
        },

        get isFormValid() {
                const confirmPassword = document.getElementById('confirmPassword').value;
                return this.passwordScore >= 5 && this.termsAccepted && this.password === confirmPassword;
        }
      
    }
}

</script>


</html>