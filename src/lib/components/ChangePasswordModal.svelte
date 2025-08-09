<script>
  import { createEventDispatcher } from 'svelte';
  console.log('ChangePasswordModal loaded');
  
  const dispatch = createEventDispatcher();
  
  export let show = false;
  
  let currentPassword = '';
  let newPassword = '';
  let confirmPassword = '';
  let loading = false;
  let error = '';
  let success = '';
  
  function closeModal() {
    console.log('ChangePasswordModal closeModal called');
    show = false;
    currentPassword = '';
    newPassword = '';
    confirmPassword = '';
    error = '';
    success = '';
    dispatch('close');
  }
  
  async function handleSubmit() {
    error = '';
    success = '';
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
      error = 'All fields are required';
      return;
    }
    
    if (newPassword !== confirmPassword) {
      error = 'New passwords do not match';
      return;
    }
    
    if (newPassword.length < 6) {
      error = 'Password must be at least 6 characters long';
      return;
    }
    
    loading = true;
    
    try {
      console.log('About to fetch:', currentPassword, newPassword);
      // Use session/cookie auth, do not send username
  const response = await fetch('http://localhost/back-ends/change_password.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          currentPassword,
          newPassword
        }),
        credentials: 'include',
      });
      console.log('Fetch response:', response);
      const result = await response.json();
      console.log('Fetch result:', result);
      if (result.success) {
        success = 'Password changed successfully!';
        setTimeout(() => {
          closeModal();
          window.location.reload();
        }, 1500);
      } else {
        error = result.message || 'Failed to change password';
      }
    } catch (err) {
      error = 'Network error. Please try again.';
      console.error('Change password error:', err);
    } finally {
      loading = false;
    }
  }

  function handleKeydown(e) {
    if (e.key === 'Escape') {
      closeModal();
    }
  }
</script>

<svelte:window on:keydown={handleKeydown} />

{#if show}
  <div class="modal-overlay" on:click={closeModal} on:keydown={handleKeydown} role="dialog" aria-modal="true">
    <div class="modal-content" on:click|stopPropagation on:keydown={handleKeydown} role="document">
      <div class="modal-header">
        <h3>Change Password</h3>
        <button class="close-btn" on:click={closeModal} aria-label="Close modal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
      
      <form on:submit|preventDefault={handleSubmit} class="password-form">
        {#if error}
          <div class="error-message">{error}</div>
        {/if}
        
        {#if success}
          <div class="success-message">{success}</div>
        {/if}
        
        <div class="form-group">
          <label for="current-password">Current Password</label>
          <input
            id="current-password"
            type="password"
            bind:value={currentPassword}
            required
            disabled={loading}
          />
        </div>
        
        <div class="form-group">
          <label for="new-password">New Password</label>
          <input
            id="new-password"
            type="password"
            bind:value={newPassword}
            required
            disabled={loading}
            minlength="6"
          />
        </div>
        
        <div class="form-group">
          <label for="confirm-password">Confirm New Password</label>
          <input
            id="confirm-password"
            type="password"
            bind:value={confirmPassword}
            required
            disabled={loading}
            minlength="6"
          />
        </div>
        
        <div class="form-actions">
          <button type="button" on:click={closeModal} disabled={loading} class="btn-cancel">
            Cancel
          </button>
          <button type="submit" disabled={loading} class="btn-submit">
            {loading ? 'Changing...' : 'Change Password'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal-content {
    background: white;
    border-radius: 8px;
    padding: 0;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
  }
  
  .modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
  }
  
  .close-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: #6b7280;
    border-radius: 4px;
    transition: all 0.2s;
  }
  
  .close-btn:hover {
    color: #374151;
    background: #f3f4f6;
  }
  
  .password-form {
    padding: 24px;
  }
  
  .form-group {
    margin-bottom: 16px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
  }
  
  .form-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
  }
  
  .form-group input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
  
  .form-group input:disabled {
    background: #f9fafb;
    color: #6b7280;
  }
  
  .error-message {
    background: #fef2f2;
    color: #dc2626;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    border: 1px solid #fecaca;
  }
  
  .success-message {
    background: #f0fdf4;
    color: #16a34a;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    border: 1px solid #bbf7d0;
  }
  
  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
  }
  
  .btn-cancel {
    padding: 8px 16px;
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
  }
  
  .btn-cancel:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
  }
  
  .btn-submit {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
  }
  
  .btn-submit:hover:not(:disabled) {
    background: #2563eb;
  }
  
  .btn-submit:disabled {
    background: #9ca3af;
    cursor: not-allowed;
  }
</style>
