<!-- src/lib/components/PersonNode.svelte -->

<!-- Add lang="ts" to the script tag -->
<script lang="ts">
  // Add explicit 'string' types to the props
  export let name: string = "Name N. Name";
  export let role: string = "Teacher";
</script>

<!-- The HTML template remains the same -->
<div class="flex w-32 flex-col items-center text-center">
  <!-- Circular Icon Container -->
  <div
    class="flex h-28 w-28 items-center justify-center rounded-full border-2 border-yellow-400 bg-green-100/80 shadow-md"
  >
    <!-- User Icon SVG -->
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="h-14 w-14 text-green-600"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      fill="none"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
      <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
      <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
    </svg>
  </div>
  <!-- Text Below Icon -->
  <div class="mt-2">
    <p class="font-semibold text-sm text-green-900">{name}</p>
    <p class="text-xs text-green-700">{role}</p>
  </div>
</div>