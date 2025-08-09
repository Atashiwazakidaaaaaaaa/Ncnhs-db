// src/lib/stores/user.ts
import { writable } from 'svelte/store';

type User = {
  username: string;
  token?: string;
  // Add other fields as needed
};

function createUserStore() {
  const storedUser = typeof localStorage !== 'undefined'
    ? JSON.parse(localStorage.getItem('user') || 'null')
    : null;

  const { subscribe, set, update } = writable<User | null>(storedUser);

  return {
    subscribe,
    set: (user: User | null) => {
      if (typeof localStorage !== 'undefined') {
        if (user) localStorage.setItem('user', JSON.stringify(user));
        else localStorage.removeItem('user');
      }
      set(user);
    },
    logout: () => {
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem('user');
      }
      set(null);
    },
    update
  };
}

export const user = createUserStore();

export function loadUserFromLocalStorage() {
  const saved = localStorage.getItem('user');
  if (saved) {
    user.set(JSON.parse(saved));
  }
}