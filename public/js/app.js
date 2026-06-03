/**
 * app.js - Fonctions utilitaires globales et gestion AJAX
 */

const API_BASE = document.querySelector('meta[property="api-base"]')?.content || 
                 window.location.pathname.split('/').slice(0, -1).join('/') + '/api/';

/**
 * Requête API générique
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };

    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(API_BASE + endpoint, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Erreur API');
        }
        
        return result;
    } catch (error) {
        console.error('Erreur API:', error);
        showError(error.message);
        throw error;
    }
}

/**
 * Affiche une notification de succès
 */
function showSuccess(message, duration = 3000) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-success';
    toast.innerHTML = `<i class="ti ti-check"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
}

/**
 * Affiche une notification d'erreur
 */
function showError(message, duration = 4000) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-error';
    toast.innerHTML = `<i class="ti ti-alert-circle"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
}

/**
 * Format nombre en FCFA
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

/**
 * Format date
 */
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('fr-FR').format(date);
}

/**
 * Confirmation avant suppression
 */
function confirmDelete(message = 'Êtes-vous sûr ?') {
    return confirm(message);
}

/**
 * Classe pour gérer les formulaires
 */
class FormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            console.error(`Formulaire ${formId} non trouvé`);
            return;
        }
    }

    /**
     * Récupère les données du formulaire
     */
    getData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    /**
     * Affiche les erreurs de validation
     */
    showErrors(errors) {
        this.clearErrors();
        
        for (let [field, messages] of Object.entries(errors)) {
            const fieldElement = this.form.querySelector(`[name="${field}"]`);
            if (fieldElement) {
                fieldElement.classList.add('is-invalid');
                const error = document.createElement('div');
                error.className = 'error-text';
                error.textContent = messages[0];
                fieldElement.parentElement.appendChild(error);
            }
        }
    }

    /**
     * Efface les erreurs
     */
    clearErrors() {
        this.form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            const error = el.parentElement.querySelector('.error-text');
            if (error) error.remove();
        });
    }

    /**
     * Réinitialise le formulaire
     */
    reset() {
        this.form.reset();
        this.clearErrors();
    }

    /**
     * Désactive/active le formulaire
     */
    setDisabled(disabled) {
        Array.from(this.form.elements).forEach(el => {
            el.disabled = disabled;
        });
    }
}

/**
 * Classe pour les listes avec pagination
 */
class ListHandler {
    constructor(tableSelector) {
        this.table = document.querySelector(tableSelector);
        this.currentPage = 1;
        this.perPage = 20;
    }

    /**
     * Charge les données
     */
    async load(endpoint, page = 1) {
        try {
            const result = await apiCall(`${endpoint}?page=${page}`);
            this.render(result.data);
            this.renderPagination(result.pagination);
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
        }
    }

    /**
     * Affiche les données dans le tableau
     */
    render(data) {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;color:var(--muted)">Aucune donnée</td></tr>';
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = this.renderRow(row);
            tbody.appendChild(tr);
        });
    }

    /**
     * À override dans les classes enfants
     */
    renderRow(row) {
        return '<td>À implémenter</td>';
    }

    /**
     * Affiche la pagination
     */
    renderPagination(pagination) {
        // À implémenter selon les besoins
    }
}

/**
 * Toast notifications CSS
 */
const toastStyles = `
<style>
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.toast-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.toast-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.is-invalid {
    border-color: var(--danger) !important;
}
.error-text {
    font-size: 11px;
    color: var(--danger);
    margin-top: 3px;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', toastStyles);