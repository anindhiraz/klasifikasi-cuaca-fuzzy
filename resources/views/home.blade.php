<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Weather Classification Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Chart.js Zoom Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <style>
        :root {
            --text-primary: #172033;
            --text-secondary: #64748b;
            --blue-main: #3b82f6;
            --card-bg: rgba(255, 255, 255, 0.68);
            --card-border: rgba(255, 255, 255, 0.60);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text-primary);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #dbeafe, #eff6ff, #f8fafc);
            transition: background 0.8s ease;
        }

        body.weather-sunny {
            background: linear-gradient(135deg, #dbeafe 0%, #eef7ff 48%, #fffaf0 100%);
        }

        body.weather-cloudy {
            background: linear-gradient(135deg, #e2e8f0 0%, #eef5ff 48%, #f8fafc 100%);
        }

        body.weather-light-rain {
            background: linear-gradient(135deg, #d6f0ff 0%, #e8f7ff 52%, #f8fbff 100%);
        }

        body.weather-rainy {
            background: linear-gradient(135deg, #cfe6ff 0%, #dbeafe 52%, #edf5ff 100%);
        }

        body.weather-stormy {
            background: linear-gradient(135deg, #d1dbe8 0%, #dbeafe 48%, #e8edff 100%);
        }

        #sky {
            position: fixed;
            inset: 0;
            z-index: -20;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 18%, rgba(59, 130, 246, 0.16), transparent 28%),
                radial-gradient(circle at 82% 10%, rgba(125, 211, 252, 0.22), transparent 30%),
                linear-gradient(135deg, #dbeafe, #eff6ff, #f8fafc);
        }

        .ambient-orb {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 999px;
            filter: blur(18px);
            opacity: 0.28;
            animation: orbFloat 12s ease-in-out infinite;
        }

        .ambient-orb.one {
            top: -120px;
            left: -80px;
            background: rgba(96, 165, 250, 0.42);
        }

        .ambient-orb.two {
            right: -100px;
            bottom: 10%;
            background: rgba(186, 230, 253, 0.50);
            animation-delay: 2s;
        }

        @keyframes orbFloat {
            0%, 100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(24px) scale(1.05);
            }
        }

        .bg-cloud {
            position: absolute;
            width: 220px;
            height: 70px;
            background: rgba(255, 255, 255, 0.36);
            border-radius: 999px;
            filter: blur(1px);
            animation: globalCloudDrift linear infinite;
        }

        .bg-cloud::before,
        .bg-cloud::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.36);
            border-radius: 50%;
        }

        .bg-cloud::before {
            width: 70px;
            height: 70px;
            left: 30px;
            top: -25px;
        }

        .bg-cloud::after {
            width: 90px;
            height: 90px;
            left: 85px;
            top: -35px;
        }

        .bg-cloud.c1 {
            top: 8%;
            left: -250px;
            animation-duration: 52s;
        }

        .bg-cloud.c2 {
            top: 35%;
            left: -350px;
            animation-duration: 76s;
        }

        .bg-cloud.c3 {
            top: 68%;
            left: -280px;
            animation-duration: 64s;
        }

        @keyframes globalCloudDrift {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(220vw);
            }
        }

        .glass {
            background: var(--card-bg);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--card-border);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        .glass-soft {
            background: rgba(255, 255, 255, 0.54);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.52);
        }

        .weather-hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            min-height: 280px;
            transition: background 0.8s ease, box-shadow 0.8s ease;
        }

        .weather-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                linear-gradient(
                    115deg,
                    transparent 0%,
                    rgba(255, 255, 255, 0.10) 38%,
                    rgba(255, 255, 255, 0.22) 48%,
                    rgba(255, 255, 255, 0.08) 58%,
                    transparent 100%
                );
            transform: translateX(-120%);
            animation: heroShimmer 9s ease-in-out infinite;
        }

        @keyframes heroShimmer {
            0% {
                transform: translateX(-120%);
                opacity: 0;
            }

            18% {
                opacity: 1;
            }

            42% {
                transform: translateX(120%);
                opacity: 0;
            }

            100% {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .hero-sunny {
            background:
                linear-gradient(135deg, rgba(219, 234, 254, 0.58), rgba(255, 255, 255, 0.28)),
                linear-gradient(135deg, rgba(255, 247, 204, 0.30), rgba(219, 234, 254, 0.26));
        }

        .hero-cloudy {
            background:
                linear-gradient(135deg, rgba(226, 232, 240, 0.40), rgba(255, 255, 255, 0.30)),
                linear-gradient(135deg, rgba(238, 242, 247, 0.34), rgba(219, 234, 254, 0.24));
        }

        .hero-light-rain {
            background:
                linear-gradient(135deg, rgba(214, 240, 255, 0.46), rgba(255, 255, 255, 0.26)),
                linear-gradient(135deg, rgba(224, 242, 254, 0.34), rgba(191, 219, 254, 0.22));
        }

        .hero-rainy {
            background:
                linear-gradient(135deg, rgba(207, 230, 255, 0.46), rgba(255, 255, 255, 0.24)),
                linear-gradient(135deg, rgba(219, 234, 254, 0.34), rgba(191, 219, 254, 0.28));
        }

        .hero-stormy {
            background:
                linear-gradient(135deg, rgba(209, 219, 232, 0.46), rgba(255, 255, 255, 0.20)),
                linear-gradient(135deg, rgba(203, 213, 225, 0.34), rgba(191, 219, 254, 0.28), rgba(224, 231, 255, 0.20));
            box-shadow:
                0 18px 50px rgba(15, 23, 42, 0.11),
                inset 0 0 80px rgba(99, 102, 241, 0.10);
        }

        .hero-content {
            position: relative;
            z-index: 3;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .hero-scene {
            position: absolute;
            top: 0;
            right: 0;
            width: 42%;
            height: 100%;
            overflow: hidden;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.54);
            border: 1px solid rgba(255, 255, 255, 0.56);
            backdrop-filter: blur(8px);
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .hero-result {
            width: 100%;
            max-width: 380px;
            padding: 18px 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.30);
            border: 1px solid rgba(255, 255, 255, 0.42);
            backdrop-filter: blur(8px);
        }

        .sun-wrap {
            position: absolute;
            top: 22px;
            right: 32px;
            width: 140px;
            height: 140px;
        }

        .sun {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 98px;
            height: 98px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fff7ae, #facc15 65%, #f59e0b 100%);
            box-shadow:
                0 0 40px rgba(250, 204, 21, 0.50),
                0 0 90px rgba(250, 204, 21, 0.25);
            animation: sunPulse 4s ease-in-out infinite;
        }

        .sun-rays {
            position: absolute;
            top: 0;
            right: 0;
            width: 126px;
            height: 126px;
            border-radius: 50%;
            border: 2px dashed rgba(250, 204, 21, 0.42);
            animation: rotateSlow 14s linear infinite;
        }

        .sun-halo {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(250, 204, 21, 0.16), rgba(250, 204, 21, 0.05), transparent 68%);
            animation: haloPulse 5s ease-in-out infinite;
        }

        @keyframes sunPulse {
            0%, 100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.06);
            }
        }

        @keyframes haloPulse {
            0%, 100% {
                transform: scale(0.95);
                opacity: 0.55;
            }

            50% {
                transform: scale(1.08);
                opacity: 0.95;
            }
        }

        @keyframes rotateSlow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .scene-cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.82);
            border-radius: 999px;
            filter: drop-shadow(0 10px 18px rgba(96, 165, 250, 0.08));
        }

        .scene-cloud::before,
        .scene-cloud::after {
            content: "";
            position: absolute;
            background: inherit;
            border-radius: 50%;
        }

        .scene-cloud.one {
            width: 138px;
            height: 48px;
            top: 42px;
            left: 8%;
            animation: cloudFloat1 8s ease-in-out infinite;
        }

        .scene-cloud.one::before {
            width: 46px;
            height: 46px;
            top: -14px;
            left: 18px;
        }

        .scene-cloud.one::after {
            width: 60px;
            height: 60px;
            top: -22px;
            left: 58px;
        }

        .scene-cloud.two {
            width: 164px;
            height: 56px;
            top: 142px;
            left: 28%;
            opacity: 0.90;
            animation: cloudFloat2 11s ease-in-out infinite;
        }

        .scene-cloud.two::before {
            width: 56px;
            height: 56px;
            top: -18px;
            left: 26px;
        }

        .scene-cloud.two::after {
            width: 72px;
            height: 72px;
            top: -26px;
            left: 74px;
        }

        .scene-cloud.three {
            width: 120px;
            height: 42px;
            top: 86px;
            left: 58%;
            opacity: 0.84;
            animation: cloudFloat3 13s ease-in-out infinite;
        }

        .scene-cloud.three::before {
            width: 42px;
            height: 42px;
            top: -14px;
            left: 18px;
        }

        .scene-cloud.three::after {
            width: 54px;
            height: 54px;
            top: -18px;
            left: 48px;
        }

        @keyframes cloudFloat1 {
            0%, 100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(28px, -8px);
            }
        }

        @keyframes cloudFloat2 {
            0%, 100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(-24px, 12px);
            }
        }

        @keyframes cloudFloat3 {
            0%, 100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(18px, -7px);
            }
        }

        .rain-wrap {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .drop {
            position: absolute;
            width: 2px;
            height: 18px;
            background: linear-gradient(to bottom, rgba(56, 189, 248, 0.12), rgba(59, 130, 246, 0.95));
            border-radius: 999px;
            animation: rainFall linear infinite;
            opacity: 0.75;
        }

        .drop.light {
            height: 13px;
            opacity: 0.50;
        }

        @keyframes rainFall {
            0% {
                transform: translateY(-30px) translateX(0);
                opacity: 0;
            }

            20% {
                opacity: 0.8;
            }

            100% {
                transform: translateY(310px) translateX(-18px);
                opacity: 0;
            }
        }

        .lightning {
            position: absolute;
            top: 62px;
            right: 90px;
            width: 0;
            height: 0;
            border-left: 12px solid transparent;
            border-right: 8px solid transparent;
            border-top: 40px solid rgba(250, 204, 21, 0.92);
            transform: rotate(12deg);
            filter: drop-shadow(0 0 10px rgba(250, 204, 21, 0.7));
            animation: flashLightning 5s infinite;
            opacity: 0;
        }

        .lightning::after {
            content: "";
            position: absolute;
            top: -8px;
            left: -8px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 6px solid transparent;
            border-top: 26px solid rgba(250, 204, 21, 0.95);
            transform: translate(2px, 28px) rotate(-8deg);
        }

        @keyframes flashLightning {
            0%, 84%, 100% {
                opacity: 0;
            }

            85% {
                opacity: 1;
            }

            87% {
                opacity: 1;
            }

            89% {
                opacity: 0;
            }
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 142px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10);
        }

        .metric-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            font-size: 22px;
        }

        .metric-button {
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(241, 245, 249, 0.82);
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .metric-button:hover {
            background: rgba(219, 234, 254, 0.92);
            color: #2563eb;
        }

        .metric-button.active {
            background: #3b82f6;
            color: white;
        }

        .hover-row {
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .hover-row:hover {
            background: rgba(219, 234, 254, 0.55);
        }

        .active-row {
            background: rgba(219, 234, 254, 0.82) !important;
        }

        .available-hour {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .available-hour:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }

        .missing-hour {
            opacity: 0.68;
            border-style: dashed;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.35);
            border-radius: 999px;
        }

        @media (max-width: 1024px) {
            .hero-scene {
                width: 40%;
                opacity: 0.72;
            }
        }

        @media (max-width: 768px) {
            .weather-hero {
                min-height: 380px;
            }

            .hero-scene {
                width: 54%;
                opacity: 0.55;
            }

            .sun-wrap {
                right: 8px;
                transform: scale(0.78);
                transform-origin: top right;
            }
        }
    </style>
</head>

<body>
    @php
        $weatherDays = collect($weatherDays)->values();
    @endphp

    <div id="sky">
        <div class="ambient-orb one"></div>
        <div class="ambient-orb two"></div>

        <div class="bg-cloud c1"></div>
        <div class="bg-cloud c2"></div>
        <div class="bg-cloud c3"></div>
    </div>

    <main class="px-4 md:px-8 pt-4 md:pt-8 pb-10 max-w-[1800px] mx-auto">

        @if ($weatherDays->isEmpty())
            <section class="glass rounded-[28px] p-8 md:p-12 text-center min-h-[420px] flex items-center justify-center">
                <div>
                    <div class="text-6xl mb-5">🌤️</div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800">
                        Belum Ada Data Cuaca
                    </h1>

                    <p class="text-slate-500 mt-3 max-w-xl mx-auto">
                        Tabel <strong>artificial_intellegence</strong> belum memiliki data pada jam
                        08:00, 09:00, 12:00, 13:00, 16:00, atau 17:00.
                    </p>
                </div>
            </section>
        @else

            <!-- =====================================================
                 HERO
            ====================================================== -->
            <section id="heroCard" class="glass weather-hero rounded-[28px] p-6 md:p-9 mb-6">
                <div id="heroScene" class="hero-bg"></div>

                <div class="hero-content h-full">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start h-full">

                        <div class="lg:col-span-7">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/50 border border-white/60 text-xs font-bold text-blue-600 mb-4">
                                FUZZY WEATHER CLASSIFICATION
                            </div>

                            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-slate-800">
                                Weather Classification Dashboard
                            </h1>

                            <p class="text-slate-500 text-base md:text-xl mt-3 max-w-3xl">
                                Fuzzy Sugeno-Based Weather Classification on LoRa Weather Station
                            </p>

                            <div class="mt-6">
                                <span class="status-badge">
                                    Selected Weather Classification:
                                    <span id="heroBadgeClassification">-</span>
                                </span>
                            </div>
                        </div>

                        <div class="lg:col-span-5 flex lg:justify-end">
                            <div class="hero-result">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.18em] font-bold text-slate-400">
                                            Fuzzy Result
                                        </div>

                                        <div id="heroClassification" class="text-3xl md:text-4xl font-extrabold text-slate-800 mt-2">
                                            -
                                        </div>
                                    </div>

                                    <div id="heroWeatherIcon" class="text-5xl md:text-6xl">
                                        ☀️
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mt-5">
                                    <div class="glass-soft rounded-2xl p-3">
                                        <div class="text-xs text-slate-400 font-semibold">
                                            Fuzzy Score
                                        </div>

                                        <div id="heroFuzzyScore" class="font-extrabold text-slate-700 mt-1">
                                            -
                                        </div>
                                    </div>

                                    <div class="glass-soft rounded-2xl p-3">
                                        <div class="text-xs text-slate-400 font-semibold">
                                            Last Observation
                                        </div>

                                        <div id="heroObservation" class="font-extrabold text-slate-700 mt-1">
                                            -
                                        </div>
                                    </div>
                                </div>

                                <div id="heroDate" class="mt-3 text-xs text-slate-400">
                                    -
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- =====================================================
                 4 WEATHER VARIABLES
            ====================================================== -->
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

                <div class="glass metric-card rounded-[24px] p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-500">Temperature</div>

                            <div id="metricTemperature" class="text-3xl font-extrabold text-slate-800 mt-2">
                                -
                            </div>

                            <div class="text-xs text-slate-400 mt-1">temperature_c</div>
                        </div>

                        <div class="metric-icon bg-blue-100 text-blue-600">
                            🌡️
                        </div>
                    </div>
                </div>

                <div class="glass metric-card rounded-[24px] p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-500">Humidity</div>

                            <div id="metricHumidity" class="text-3xl font-extrabold text-slate-800 mt-2">
                                -
                            </div>

                            <div class="text-xs text-slate-400 mt-1">humidity_percent</div>
                        </div>

                        <div class="metric-icon bg-emerald-100 text-emerald-600">
                            💧
                        </div>
                    </div>
                </div>

                <div class="glass metric-card rounded-[24px] p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-500">Irradiance</div>

                            <div id="metricIrradiance" class="text-3xl font-extrabold text-slate-800 mt-2">
                                -
                            </div>

                            <div class="text-xs text-slate-400 mt-1">irradiance</div>
                        </div>

                        <div class="metric-icon bg-amber-100 text-amber-600">
                            ☀️
                        </div>
                    </div>
                </div>

                <div class="glass metric-card rounded-[24px] p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-500">Precipitation</div>

                            <div id="metricPrecipitation" class="text-3xl font-extrabold text-slate-800 mt-2">
                                -
                            </div>

                            <div class="text-xs text-slate-400 mt-1">precipitation_mm_per_hr</div>
                        </div>

                        <div class="metric-icon bg-violet-100 text-violet-600">
                            🌧️
                        </div>
                    </div>
                </div>

            </section>

            <!-- =====================================================
                 7 DAYS + 7 DAY CHART
            ====================================================== -->
            <section class="grid grid-cols-12 gap-6 items-stretch mb-6">

                <!-- 7 DAY TABLE -->
                <div class="col-span-12 xl:col-span-4">
                    <div class="glass rounded-[24px] p-6 h-full">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="font-extrabold text-xl text-slate-800">
                                    7-Day Weather Classification
                                </h2>

                                <p class="text-xs text-slate-500 mt-1">
                                    Klasifikasi memakai record terakhir yang tersedia pada setiap hari.
                                </p>
                            </div>

                            <span class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-600 font-bold">
                                7 Days
                            </span>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-white/60 bg-white/45">
                            <table class="w-full text-sm">
                                <thead class="bg-white/80 backdrop-blur-md text-slate-500">
                                    <tr>
                                        <th class="text-left px-4 py-3 font-semibold">Date</th>
                                        <th class="text-left px-4 py-3 font-semibold">Classification</th>
                                        <th class="text-right px-4 py-3 font-semibold">Temp</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($weatherDays as $day)
                                        <tr
                                            class="hover-row cursor-pointer"
                                            data-day-index="{{ $loop->index }}"
                                            onclick="selectDay({{ $loop->index }})"
                                        >
                                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                                <div class="font-semibold">
                                                    {{ $day['date_label'] }}
                                                </div>

                                                <div class="text-xs text-slate-400 mt-0.5">
                                                    {{ $day['latest']['time'] ?? '-' }}
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">
                                                <span
                                                    class="classification-cell"
                                                    data-classification="{{ $day['latest']['fuzzy_classification'] ?? '' }}"
                                                ></span>
                                            </td>

                                            <td class="px-4 py-3 text-right text-blue-500 font-extrabold whitespace-nowrap">
                                                @if ($day['latest'])
                                                    {{ number_format($day['latest']['temperature'], 2) }}°
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 7 DAY CHART -->
                <div class="col-span-12 xl:col-span-8">
                    <div class="glass rounded-[24px] p-6 h-full">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                            <div>
                                <h2 class="font-extrabold text-xl text-slate-800">
                                    7-Day Weather Analytics
                                </h2>

                                <p class="text-sm text-slate-500 mt-1">
                                    Nilai grafik merupakan rata-rata data aktual pada jam observasi yang tersedia.
                                    Klik titik pada grafik untuk membuka detail hari tersebut.
                                </p>
                            </div>

                            <button
                                type="button"
                                onclick="resetDailyZoom()"
                                class="shrink-0 px-3 py-2 rounded-xl bg-blue-500 text-white text-xs font-bold hover:bg-blue-600 transition"
                            >
                                Reset Zoom
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <button class="metric-button daily-metric active" data-metric="temperature" onclick="setDailyMetric('temperature')">
                                Temperature
                            </button>

                            <button class="metric-button daily-metric" data-metric="humidity" onclick="setDailyMetric('humidity')">
                                Humidity
                            </button>

                            <button class="metric-button daily-metric" data-metric="irradiance" onclick="setDailyMetric('irradiance')">
                                Irradiance
                            </button>

                            <button class="metric-button daily-metric" data-metric="precipitation" onclick="setDailyMetric('precipitation')">
                                Precipitation
                            </button>
                        </div>

                        <div class="h-[310px]">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>

            </section>

            <!-- =====================================================
                 HOURLY CHART + HOURLY CARDS
            ====================================================== -->
            <section class="grid grid-cols-12 gap-6 items-stretch">

                <!-- HOURLY CHART -->
                <div class="col-span-12 xl:col-span-7">
                    <div class="glass rounded-[24px] p-6 h-full">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center flex-wrap gap-3">
                                    <h2 class="font-extrabold text-xl text-slate-800">
                                        Daily Detail Chart
                                    </h2>

                                    <span id="selectedDateLabel" class="text-xs px-3 py-1 rounded-full bg-slate-100/90 text-slate-600 font-bold">
                                        -
                                    </span>
                                </div>

                                <p class="text-sm text-slate-500 mt-1">
                                    Detail data aktual per jam: 08:00, 09:00, 12:00, 13:00, 16:00, 17:00.
                                </p>
                            </div>

                            <button
                                type="button"
                                onclick="resetHourlyZoom()"
                                class="shrink-0 px-3 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-700 transition"
                            >
                                Reset Zoom
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <button class="metric-button hourly-metric active" data-metric="temperature" onclick="setHourlyMetric('temperature')">
                                Temperature
                            </button>

                            <button class="metric-button hourly-metric" data-metric="humidity" onclick="setHourlyMetric('humidity')">
                                Humidity
                            </button>

                            <button class="metric-button hourly-metric" data-metric="irradiance" onclick="setHourlyMetric('irradiance')">
                                Irradiance
                            </button>

                            <button class="metric-button hourly-metric" data-metric="precipitation" onclick="setHourlyMetric('precipitation')">
                                Precipitation
                            </button>
                        </div>

                        <div class="h-[340px]">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- HOURLY CARDS -->
                <div class="col-span-12 xl:col-span-5">
                    <div class="glass rounded-[24px] p-6 h-full">
                        <div class="mb-4">
                            <h2 class="font-extrabold text-xl text-slate-800">
                                Hourly Weather Data
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Setiap kartu membaca record sebenarnya dari PostgreSQL.
                            </p>
                        </div>

                        <div id="hourlyBox" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                    </div>
                </div>

            </section>

        @endif
    </main>

    @if ($weatherDays->isNotEmpty())
        <script>
            const weatherDays = @json($weatherDays);
            const targetHours = @json($targetHours);

            let selectedDayIndex = 0;
            let selectedDailyMetric = "temperature";
            let selectedHourlyMetric = "temperature";

            const weatherTypes = {
                sunny: {
                    icon: "☀️",
                    label: "Cerah",
                    heroClass: "hero-sunny",
                    bodyClass: "weather-sunny",
                    colorClass: "text-amber-500",
                },

                cloudy: {
                    icon: "☁️",
                    label: "Berawan",
                    heroClass: "hero-cloudy",
                    bodyClass: "weather-cloudy",
                    colorClass: "text-slate-500",
                },

                lightRain: {
                    icon: "🌦️",
                    label: "Hujan Ringan",
                    heroClass: "hero-light-rain",
                    bodyClass: "weather-light-rain",
                    colorClass: "text-sky-500",
                },

                rain: {
                    icon: "🌧️",
                    label: "Hujan",
                    heroClass: "hero-rainy",
                    bodyClass: "weather-rainy",
                    colorClass: "text-blue-600",
                },

                storm: {
                    icon: "⛈️",
                    label: "Badai",
                    heroClass: "hero-stormy",
                    bodyClass: "weather-stormy",
                    colorClass: "text-violet-600",
                },
            };

            const metricConfig = {
                temperature: {
                    label: "Temperature (°C)",
                    unit: "°C",
                    borderColor: "#3b82f6",
                    backgroundColor: "rgba(59, 130, 246, 0.12)",
                },

                humidity: {
                    label: "Humidity (%)",
                    unit: "%",
                    borderColor: "#10b981",
                    backgroundColor: "rgba(16, 185, 129, 0.12)",
                },

                irradiance: {
                    label: "Irradiance",
                    unit: "",
                    borderColor: "#f59e0b",
                    backgroundColor: "rgba(245, 158, 11, 0.12)",
                },

                precipitation: {
                    label: "Precipitation (mm/hr)",
                    unit: " mm/hr",
                    borderColor: "#8b5cf6",
                    backgroundColor: "rgba(139, 92, 246, 0.12)",
                },
            };

            function normalizeWeatherType(classification) {
                const value = String(classification || "").toLowerCase();

                if (value.includes("badai") || value.includes("storm")) {
                    return "storm";
                }

                if (
                    value.includes("hujan ringan") ||
                    value.includes("light rain") ||
                    value.includes("drizzle")
                ) {
                    return "lightRain";
                }

                if (value.includes("hujan") || value.includes("rain")) {
                    return "rain";
                }

                if (value.includes("berawan") || value.includes("cloud")) {
                    return "cloudy";
                }

                return "sunny";
            }

            function getWeatherMeta(classification) {
                return weatherTypes[normalizeWeatherType(classification)] || weatherTypes.sunny;
            }

            function formatValue(value, decimals = 2) {
                if (value === null || value === undefined || value === "") {
                    return "-";
                }

                const numeric = Number(value);

                if (Number.isNaN(numeric)) {
                    return String(value);
                }

                return numeric.toFixed(decimals);
            }

            function generateDrops(count, type = "normal") {
                let drops = "";

                for (let i = 0; i < count; i++) {
                    const left = 8 + (i * (type === "light" ? 3.6 : 2.3));
                    const top = Math.floor(18 + Math.random() * 130);
                    const duration = type === "light"
                        ? (1.25 + Math.random() * 0.6).toFixed(2)
                        : (0.75 + Math.random() * 0.45).toFixed(2);
                    const delay = (Math.random() * 2).toFixed(2);

                    drops += `
                        <div
                            class="drop ${type === "light" ? "light" : ""}"
                            style="
                                left: ${left}%;
                                top: ${top}px;
                                animation-duration: ${duration}s;
                                animation-delay: ${delay}s;
                            "
                        ></div>
                    `;
                }

                return drops;
            }

            function renderWeatherScene(classification) {
                const type = normalizeWeatherType(classification);

                if (type === "sunny") {
                    return `
                        <div class="hero-scene">
                            <div class="sun-wrap">
                                <div class="sun-halo"></div>
                                <div class="sun-rays"></div>
                                <div class="sun"></div>
                            </div>

                            <div class="scene-cloud one"></div>
                            <div class="scene-cloud two"></div>
                            <div class="scene-cloud three"></div>
                        </div>
                    `;
                }

                if (type === "cloudy") {
                    return `
                        <div class="hero-scene">
                            <div class="scene-cloud one"></div>
                            <div class="scene-cloud two"></div>
                            <div class="scene-cloud three"></div>
                        </div>
                    `;
                }

                if (type === "lightRain") {
                    return `
                        <div class="hero-scene">
                            <div class="scene-cloud one" style="background: rgba(241, 245, 249, 0.92);"></div>
                            <div class="scene-cloud two" style="background: rgba(226, 232, 240, 0.90);"></div>

                            <div class="rain-wrap">
                                ${generateDrops(16, "light")}
                            </div>
                        </div>
                    `;
                }

                if (type === "rain") {
                    return `
                        <div class="hero-scene">
                            <div class="scene-cloud one" style="background: rgba(226, 232, 240, 0.94);"></div>
                            <div class="scene-cloud two" style="background: rgba(203, 213, 225, 0.92);"></div>

                            <div class="rain-wrap">
                                ${generateDrops(28, "normal")}
                            </div>
                        </div>
                    `;
                }

                return `
                    <div class="hero-scene">
                        <div class="scene-cloud one" style="background: rgba(203, 213, 225, 0.94);"></div>
                        <div class="scene-cloud two" style="background: rgba(148, 163, 184, 0.88);"></div>
                        <div class="scene-cloud three" style="background: rgba(203, 213, 225, 0.90);"></div>

                        <div class="rain-wrap">
                            ${generateDrops(34, "normal")}
                        </div>

                        <div class="lightning"></div>
                    </div>
                `;
            }

            function hydrateClassificationCells() {
                document.querySelectorAll(".classification-cell").forEach((cell) => {
                    const classification = cell.dataset.classification;

                    if (!classification) {
                        cell.textContent = "-";
                        return;
                    }

                    const meta = getWeatherMeta(classification);

                    cell.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <span>${meta.icon}</span>
                            <span>${classification}</span>
                        </span>
                    `;
                });
            }

            function updateHero(day) {
                const latest = day.latest;

                if (!latest) {
                    return;
                }

                const meta = getWeatherMeta(latest.fuzzy_classification);
                const heroCard = document.getElementById("heroCard");
                const heroScene = document.getElementById("heroScene");

                document.body.classList.remove(
                    "weather-sunny",
                    "weather-cloudy",
                    "weather-light-rain",
                    "weather-rainy",
                    "weather-stormy"
                );

                document.body.classList.add(meta.bodyClass);

                heroCard.classList.remove(
                    "hero-sunny",
                    "hero-cloudy",
                    "hero-light-rain",
                    "hero-rainy",
                    "hero-stormy"
                );

                heroCard.classList.add(meta.heroClass);

                heroScene.innerHTML = renderWeatherScene(latest.fuzzy_classification);

                document.getElementById("heroBadgeClassification").textContent =
                    latest.fuzzy_classification || "-";

                document.getElementById("heroClassification").textContent =
                    latest.fuzzy_classification || "-";

                document.getElementById("heroClassification").className =
                    `text-3xl md:text-4xl font-extrabold mt-2 ${meta.colorClass}`;

                document.getElementById("heroWeatherIcon").textContent = meta.icon;

                document.getElementById("heroFuzzyScore").textContent =
                    latest.fuzzy_score === null || latest.fuzzy_score === undefined
                        ? "-"
                        : latest.fuzzy_score;

                document.getElementById("heroObservation").textContent =
                    latest.time || "-";

                document.getElementById("heroDate").textContent =
                    `Data record: ${latest.date_time || day.date_label}`;

                document.getElementById("metricTemperature").textContent =
                    `${formatValue(latest.temperature)} °C`;

                document.getElementById("metricHumidity").textContent =
                    `${formatValue(latest.humidity)} %`;

                document.getElementById("metricIrradiance").textContent =
                    formatValue(latest.irradiance);

                document.getElementById("metricPrecipitation").textContent =
                    `${formatValue(latest.precipitation)} mm/hr`;

                gsap.fromTo(
                    "#heroClassification, #heroWeatherIcon, #heroFuzzyScore, #heroObservation",
                    { opacity: 0, y: 8 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.45,
                        stagger: 0.04,
                        ease: "power2.out"
                    }
                );
            }

            function renderHourlyCards(day) {
                const box = document.getElementById("hourlyBox");

                box.innerHTML = day.hourly.map((hour) => {
                    if (!hour.available) {
                        return `
                            <div class="glass-soft missing-hour rounded-2xl p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-extrabold text-slate-700">
                                        ${hour.time}
                                    </div>

                                    <div class="text-lg">
                                        ⏱️
                                    </div>
                                </div>

                                <div class="text-sm font-bold text-slate-500 mt-3">
                                    Data tidak tersedia
                                </div>

                                <div class="text-xs text-slate-400 mt-1">
                                    Tidak ada record PostgreSQL pada jam ini.
                                </div>
                            </div>
                        `;
                    }

                    const meta = getWeatherMeta(hour.fuzzy_classification);

                    return `
                        <div class="glass-soft available-hour rounded-2xl p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-extrabold text-slate-700">
                                        ${hour.time}
                                    </div>

                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Fuzzy Score: ${hour.fuzzy_score ?? "-"}
                                    </div>
                                </div>

                                <div class="text-2xl">
                                    ${meta.icon}
                                </div>
                            </div>

                            <div class="text-sm font-extrabold text-slate-700 mt-3">
                                ${hour.fuzzy_classification || "-"}
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                                <div class="rounded-xl bg-white/55 p-2">
                                    <div class="text-slate-400">Temp</div>
                                    <div class="font-bold text-blue-600 mt-0.5">
                                        ${formatValue(hour.temperature)} °C
                                    </div>
                                </div>

                                <div class="rounded-xl bg-white/55 p-2">
                                    <div class="text-slate-400">Humidity</div>
                                    <div class="font-bold text-emerald-600 mt-0.5">
                                        ${formatValue(hour.humidity)} %
                                    </div>
                                </div>

                                <div class="rounded-xl bg-white/55 p-2">
                                    <div class="text-slate-400">Irradiance</div>
                                    <div class="font-bold text-amber-600 mt-0.5">
                                        ${formatValue(hour.irradiance)}
                                    </div>
                                </div>

                                <div class="rounded-xl bg-white/55 p-2">
                                    <div class="text-slate-400">Precipitation</div>
                                    <div class="font-bold text-violet-600 mt-0.5">
                                        ${formatValue(hour.precipitation)} mm/hr
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join("");

                gsap.fromTo(
                    "#hourlyBox > div",
                    { opacity: 0, y: 10 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.35,
                        stagger: 0.04,
                        ease: "power2.out"
                    }
                );
            }

            function setActiveTableRow(index) {
                document.querySelectorAll("[data-day-index]").forEach((row) => {
                    row.classList.remove("active-row");
                });

                const activeRow = document.querySelector(`[data-day-index="${index}"]`);

                if (activeRow) {
                    activeRow.classList.add("active-row");
                }
            }

            function updateMetricButtons(selector, metric) {
                document.querySelectorAll(selector).forEach((button) => {
                    button.classList.toggle(
                        "active",
                        button.dataset.metric === metric
                    );
                });
            }

            const dailyChart = new Chart(
                document.getElementById("dailyChart"),
                {
                    type: "line",

                    data: {
                        labels: weatherDays.map((day) => day.date_label),

                        datasets: [
                            {
                                label: metricConfig.temperature.label,
                                data: weatherDays.map((day) => day.averages.temperature),
                                borderColor: metricConfig.temperature.borderColor,
                                backgroundColor: metricConfig.temperature.backgroundColor,
                                fill: true,
                                tension: 0.38,
                                borderWidth: 3,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: metricConfig.temperature.borderColor,
                            }
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: "nearest",
                            intersect: false,
                        },

                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                selectDay(elements[0].index);
                            }
                        },

                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                                labels: {
                                    color: "#475569",
                                    usePointStyle: true,
                                    padding: 16,
                                    font: {
                                        size: 12,
                                        weight: "600",
                                    },
                                },
                            },

                            tooltip: {
                                backgroundColor: "rgba(15, 23, 42, 0.94)",
                                titleColor: "#ffffff",
                                bodyColor: "#e2e8f0",
                                padding: 12,
                                cornerRadius: 12,
                            },

                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: "x",
                                },

                                zoom: {
                                    wheel: {
                                        enabled: true,
                                    },

                                    pinch: {
                                        enabled: true,
                                    },

                                    mode: "x",
                                },
                            },
                        },

                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },

                                ticks: {
                                    color: "#64748b",
                                },
                            },

                            y: {
                                grid: {
                                    color: "rgba(148, 163, 184, 0.18)",
                                },

                                ticks: {
                                    color: "#64748b",
                                },
                            },
                        },
                    },
                }
            );

            const hourlyChart = new Chart(
                document.getElementById("hourlyChart"),
                {
                    type: "line",

                    data: {
                        labels: targetHours,

                        datasets: [
                            {
                                label: metricConfig.temperature.label,
                                data: [],
                                borderColor: metricConfig.temperature.borderColor,
                                backgroundColor: metricConfig.temperature.backgroundColor,
                                fill: true,
                                tension: 0.35,
                                borderWidth: 3,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: metricConfig.temperature.borderColor,
                                spanGaps: false,
                            }
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: "index",
                            intersect: false,
                        },

                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                                labels: {
                                    color: "#475569",
                                    usePointStyle: true,
                                    padding: 16,
                                    font: {
                                        size: 12,
                                        weight: "600",
                                    },
                                },
                            },

                            tooltip: {
                                backgroundColor: "rgba(15, 23, 42, 0.94)",
                                titleColor: "#ffffff",
                                bodyColor: "#e2e8f0",
                                padding: 12,
                                cornerRadius: 12,
                            },

                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: "x",
                                },

                                zoom: {
                                    wheel: {
                                        enabled: true,
                                    },

                                    pinch: {
                                        enabled: true,
                                    },

                                    mode: "x",
                                },
                            },
                        },

                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },

                                ticks: {
                                    color: "#64748b",
                                },
                            },

                            y: {
                                grid: {
                                    color: "rgba(148, 163, 184, 0.18)",
                                },

                                ticks: {
                                    color: "#64748b",
                                },
                            },
                        },
                    },
                }
            );

            function setDailyMetric(metric) {
                selectedDailyMetric = metric;

                const config = metricConfig[metric];

                dailyChart.data.datasets[0].label = config.label;
                dailyChart.data.datasets[0].data =
                    weatherDays.map((day) => day.averages[metric]);

                dailyChart.data.datasets[0].borderColor = config.borderColor;
                dailyChart.data.datasets[0].backgroundColor = config.backgroundColor;
                dailyChart.data.datasets[0].pointBackgroundColor = config.borderColor;

                dailyChart.update();

                updateMetricButtons(".daily-metric", metric);
            }

            function setHourlyMetric(metric) {
                selectedHourlyMetric = metric;

                const day = weatherDays[selectedDayIndex];
                const config = metricConfig[metric];

                hourlyChart.data.datasets[0].label = config.label;

                hourlyChart.data.datasets[0].data =
                    day.hourly.map((hour) => hour.available ? hour[metric] : null);

                hourlyChart.data.datasets[0].borderColor = config.borderColor;
                hourlyChart.data.datasets[0].backgroundColor = config.backgroundColor;
                hourlyChart.data.datasets[0].pointBackgroundColor = config.borderColor;

                hourlyChart.update();

                updateMetricButtons(".hourly-metric", metric);
            }

            function resetDailyZoom() {
                if (typeof dailyChart.resetZoom === "function") {
                    dailyChart.resetZoom();
                }
            }

            function resetHourlyZoom() {
                if (typeof hourlyChart.resetZoom === "function") {
                    hourlyChart.resetZoom();
                }
            }

            function selectDay(index) {
                const day = weatherDays[index];

                if (!day) {
                    return;
                }

                selectedDayIndex = index;

                updateHero(day);

                document.getElementById("selectedDateLabel").textContent =
                    day.date_label;

                setActiveTableRow(index);

                renderHourlyCards(day);

                setHourlyMetric(selectedHourlyMetric);

                if (typeof hourlyChart.resetZoom === "function") {
                    hourlyChart.resetZoom();
                }
            }

            hydrateClassificationCells();

            selectDay(0);

            gsap.from(".weather-hero", {
                y: 24,
                opacity: 0,
                duration: 0.9,
                ease: "power3.out",
            });

            gsap.from(".metric-card", {
                y: 18,
                opacity: 0,
                duration: 0.7,
                stagger: 0.06,
                delay: 0.12,
                ease: "power3.out",
            });

            gsap.from(".glass:not(.weather-hero):not(.metric-card)", {
                y: 18,
                opacity: 0,
                duration: 0.75,
                stagger: 0.06,
                delay: 0.18,
                ease: "power3.out",
            });
        </script>
    @endif
</body>
</html>