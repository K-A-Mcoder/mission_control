 <form action="/logout" method="POST" class="dropdown-item">
     <?= csrf_field() ?>
     <button type="submit"
         class="px-[1.25rem] py-[0.6rem] w-full text-left group">
         <svg class="profle-logout inline-block" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff7979" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
             <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
             <polyline points="16 17 21 12 16 7"></polyline>
             <line x1="21" y1="12" x2="9" y2="12"></line>
         </svg>
         <span class="ml-2 text-danger text-[13px]">Logout </span>
     </button>
 </form>