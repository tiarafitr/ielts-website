<template>
  <div class="ambience" aria-hidden="true">
    <span class="blob blob--1"></span>
    <span class="blob blob--2"></span>
    <span class="blob blob--3"></span>

    <svg class="floaty floaty--a" width="54" height="54" viewBox="0 0 54 54" fill="none">
      <path d="M8 12a6 6 0 0 1 6-6h26a6 6 0 0 1 6 6v16a6 6 0 0 1-6 6H24l-10 8v-8a6 6 0 0 1-6-6V12Z"
            fill="#fff" stroke="#cdbcff" stroke-width="2" />
      <circle cx="21" cy="20" r="2.4" fill="#b8a6ff" />
      <circle cx="28" cy="20" r="2.4" fill="#b8a6ff" />
      <circle cx="35" cy="20" r="2.4" fill="#b8a6ff" />
    </svg>

    <svg class="floaty floaty--b" width="46" height="46" viewBox="0 0 46 46" fill="none">
      <rect x="17" y="6" width="12" height="22" rx="6" fill="#fff" stroke="#9ed8ff" stroke-width="2" />
      <path d="M12 22a11 11 0 0 0 22 0M23 33v7" stroke="#9ed8ff" stroke-width="2" stroke-linecap="round" />
    </svg>

    <svg class="floaty floaty--c" width="42" height="42" viewBox="0 0 42 42" fill="none">
      <path d="M6 10h12a5 5 0 0 1 5 5v19a4 4 0 0 0-4-4H6V10Z" fill="#fff" stroke="#9fe6d4" stroke-width="2" />
      <path d="M36 10H24a5 5 0 0 0-5 5v19a4 4 0 0 1 4-4h13V10Z" fill="#fff" stroke="#9fe6d4" stroke-width="2" />
    </svg>
  </div>
</template>

<script>
export default { name: 'AmbientBackdrop' };
</script>
