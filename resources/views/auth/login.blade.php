<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login • PARKIEST</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            min-height: screen;
            background: radial-gradient(circle at top left, #FFDAB9 0%, #F8C8C4 40%, #FFCC99 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: sans-serif;
        }

        .scene {
            position: relative;
            height: 320px;
            border-radius: 32px;
            overflow: hidden;
            background: linear-gradient(135deg, #FFF5E1 0%, #FFDAB9 100%);
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.02);
        }

        .road {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 40px;
            height: 80px;
            background: #4A4A4A;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .road::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: repeating-linear-gradient(to right, #FFFFFF 0 20px, transparent 20px 40px);
            transform: translateY(-50%);
            opacity: 0.3;
        }

        .parking-machine {
            position: absolute;
            left: 200px;
            bottom: 90px;
            width: 30px;
            height: 60px;
            background: #FFFFFF;
            border-radius: 8px;
            z-index: 10;
            border: 2px solid #FFE4B5;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.05);
        }

        .parking-machine::before {
            content: "";
            position: absolute;
            top: 8px;
            left: 5px;
            width: 20px;
            height: 14px;
            background: #4A4A4A;
            border-radius: 3px;
        }

        .barrier-base {
            position: absolute;
            left: 120px;
            bottom: 90px;
            width: 30px;
            height: 48px;
            background: #FFFFFF;
            border-radius: 4px;
            z-index: 10;
        }

        .arm {
            position: absolute;
            left: 130px;
            bottom: 112px;
            width: 110px;
            height: 8px;
            background: repeating-linear-gradient(45deg, #FF6B6B, #FF6B6B 10px, #FFFFFF 10px, #FFFFFF 20px);
            border-radius: 4px;
            transform-origin: left center;
            z-index: 11;
            animation: armRaise 7s infinite ease-in-out;
        }

        .login-section {
            background-color: #f1b560;
        }

        .car-real {
            position: absolute;
            bottom: 45px;
            width: 130px;
            z-index: 15;
            right: -150px;
            animation: carDrive 7s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes carDrive {
            0% {
                right: -150px;
            }

            30% {
                right: 180px;
            }

            60% {
                right: 180px;
            }

            100% {
                right: 110%;
            }
        }

        @keyframes armRaise {

            0%,
            55% {
                transform: rotate(60deg);
            }

            65%,
            90% {
                transform: rotate(-90deg);
            }

            100% {
                transform: rotate(60deg);
            }
        }
    </style>

<body class="min-h-screen bg-[#FFF9F0] flex items-center justify-center p-6 font-sans">
    <div class="w-full max-w-6xl rounded-[3rem] overflow-hidden shadow-2xl bg-white border border-orange-50">
        <div class="grid md:grid-cols-2">

            <div class="p-12 bg-white">
                <h1 class="text-3xl font-black text-slate-800 mb-8 tracking-tight">PARKIEST</span></h1>

                <div class="scene">
                    <div class="road"></div>
                    <div class="parking-machine"></div>
                    <div class="barrier-base"></div>
                    <div class="arm"></div>
                    <img src="https://cdn-icons-png.flaticon.com/512/744/744465.png" class="car-real">
                </div>

                <p class="mt-6 text-xs text-slate-400 italic">Manage Parking Easier and Smarter</p>
            </div>

            <div class="p-12 login-section flex flex-col justify-center text-center">
                <h2 class="text-6xl font-black text-white mb-2 drop-shadow-sm">WELCOME!</h2>
                <p class="text-orange-900/40 font-bold text-xs uppercase tracking-widest mb-10">PARKIEST - Smart Parking Management System</p>

                <form method="POST" action="{{ route('login') }}" class="space-y-4 text-left">
                    @csrf
                    <input type="email" name="email" placeholder="Email Address" required
                        class="w-full px-6 py-4 rounded-2xl border-none bg-white/90 focus:ring-4 focus:ring-orange-200 transition-all text-slate-700">

                    <input type="password" name="password" placeholder="Password" required
                        class="w-full px-6 py-4 rounded-2xl border-none bg-white/90 focus:ring-4 focus:ring-orange-200 transition-all text-slate-700">

                    <button type="submit"
                        class="w-full bg-white text-orange-500 font-black py-4 rounded-2xl shadow-lg hover:bg-orange-50 transition-all active:scale-95 tracking-widest text-sm">
                        LOG IN
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>

</html>
