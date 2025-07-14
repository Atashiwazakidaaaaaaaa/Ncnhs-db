<!-- src/routes/+page.svelte -->
<script>
  import PersonNode from "$lib/components/PersonNode.svelte";
</script>

<!-- Main container with background image -->
<div class="relative min-h-screen bg-cover bg-center font-sans" style="background-image: url('/school-bg.jpg');">
  <!-- Gradient Overlay -->
  <div class="absolute inset-0 bg-gradient-to-b from-green-900/30 via-transparent to-yellow-400/40"></div>

  <!-- Page Content (positioned above overlay) -->
  <div class="relative z-10 flex min-h-screen flex-col">
    <!-- Header -->
    <header class="w-full bg-green-900/90 p-3 shadow-lg backdrop-blur-sm">
      <div class="container mx-auto flex items-center justify-between">
        <!-- Left Side: Icon and School Name -->
        <div class="flex items-center gap-3 text-white">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h1 class="hidden font-bold tracking-wide sm:block md:text-xl">
            NEW CABALAN NATIONAL HIGH SCHOOL
          </h1>
          <h1 class="block font-bold sm:hidden">NCNHS</h1>
        </div>

        <!-- Right Side: Navigation -->
        <nav class="flex items-center gap-1 md:gap-2">
          <a href="#" class="px-3 py-2 rounded-md text-xs md:text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors">ABOUT</a>
          <a href="#" class="px-3 py-2 rounded-md text-xs md:text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors">ANNOUNCEMENTS</a>
          <a href="#" class="px-3 py-2 rounded-md text-xs md:text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors">CALENDAR</a>
          <a href="#" class="px-3 py-2 rounded-md text-xs md:text-sm font-semibold text-green-900 bg-yellow-400 shadow-inner">FACULTY</a>
        </nav>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center p-4">
      <!-- School Logo -->
      <div class="my-6">
        <div class="flex h-36 w-36 items-center justify-center rounded-full bg-yellow-400 p-1 shadow-xl md:h-40 md:w-40">
          <div class="flex h-full w-full flex-col items-center justify-center rounded-full bg-white text-center">
            <span class="text-[0.6rem] font-bold text-green-700">NEW CABALAN NATIONAL</span>
            <span class="text-xs font-bold text-green-700">HIGH SCHOOL</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 my-1" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
            <span class="text-xs font-bold text-green-700">1979</span>
          </div>
        </div>
      </div>
      
      <!-- Organizational Chart Container -->
      <div class="mb-12 w-full max-w-6xl rounded-3xl border-[12px] border-yellow-400/80 bg-white/95 p-4 shadow-2xl backdrop-blur-sm sm:p-8">
        <!-- Chart Canvas: using absolute positioning for nodes and lines -->
        <div class="relative w-full min-h-[66rem] md:min-h-[60rem]">
          <!-- NODES -->
          <!-- Row 1 -->
          <div class="absolute top-0 left-1/2 -translate-x-1/2"><PersonNode name="Name N. Name" role="Principal" /></div>
          
          <!-- Row 2 -->
          <div class="absolute top-[10rem] left-[15%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 1" /></div>
          <div class="absolute top-[10rem] left-1/2 -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 2" /></div>
          <div class="absolute top-[10rem] left-[85%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 3" /></div>
          
          <!-- Row 3 -->
          <div class="absolute top-[22rem] left-[5%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 4" /></div>
          <div class="absolute top-[22rem] left-[25%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 5" /></div>
          <div class="absolute top-[22rem] left-[75%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 6" /></div>
          <div class="absolute top-[22rem] left-[95%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 7" /></div>

          <!-- Row 4 -->
          <div class="absolute top-[38rem] left-[30%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 8" /></div>
          <div class="absolute top-[38rem] left-1/2 -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 9" /></div>
          <div class="absolute top-[38rem] left-[70%] -translate-x-1/2"><PersonNode name="Name N. Name" role="Teacher 10" /></div>

          <!-- CONNECTING LINES -->
          <div class="absolute top-0 left-0 h-full w-full -z-10">
            <!-- Principal to Row 2 -->
            <div class="absolute top-[7rem] left-1/2 h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- Principal vertical stem -->
            <div class="absolute top-[8rem] left-[15%] w-[70%] h-0.5 bg-green-500"></div> <!-- Horizontal line for Row 2 -->
            <div class="absolute top-[8rem] left-[15%] h-8 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T1 connector -->
            <div class="absolute top-[8rem] left-1/2 h-8 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T2 connector -->
            <div class="absolute top-[8rem] left-[85%] h-8 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T3 connector -->

            <!-- T1 to Row 3 (T4, T5) -->
            <div class="absolute top-[17rem] left-[15%] h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T1 vertical stem -->
            <div class="absolute top-[18rem] left-[5%] w-[20%] h-0.5 bg-green-500"></div> <!-- Horizontal line for T4, T5 -->
            <div class="absolute top-[18rem] left-[5%] h-16 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T4 connector -->
            <div class="absolute top-[18rem] left-[25%] h-16 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T5 connector -->

            <!-- T3 to Row 3 (T6, T7) -->
            <div class="absolute top-[17rem] left-[85%] h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T3 vertical stem -->
            <div class="absolute top-[18rem] left-[75%] w-[20%] h-0.5 bg-green-500"></div> <!-- Horizontal line for T6, T7 -->
            <div class="absolute top-[18rem] left-[75%] h-16 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T6 connector -->
            <div class="absolute top-[18rem] left-[95%] h-16 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T7 connector -->

            <!-- T2 to Row 4 (T8, T9, T10) -->
            <div class="absolute top-[17rem] left-1/2 h-80 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T2 long vertical stem -->
            <div class="absolute top-[37rem] left-[30%] w-[40%] h-0.5 bg-green-500"></div> <!-- Horizontal line for Row 4 -->
            <div class="absolute top-[37rem] left-[30%] h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T8 connector -->
            <div class="absolute top-[37rem] left-1/2 h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T9 connector -->
            <div class="absolute top-[37rem] left-[70%] h-4 w-0.5 -translate-x-1/2 bg-green-500"></div> <!-- T10 connector -->
          </div>
        </div>
      </div>
    </main>
  </div>
</div>