import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        show: false,
        type: 'success', // 'success' | 'error' | 'warning' | 'info'
        message: '',
        notify(message, type = 'success') {
            this.message = message;
            this.type = type;
            this.show = true;
            setTimeout(() => {
                this.show = false;
            }, 4500);
        },
        close() {
            this.show = false;
        }
    });

    Alpine.store('cart', {
        items: [],
        isOpen: false,
        maxItems: 3,

        init() {
            try {
                const saved = localStorage.getItem('perpus24_cart_books');
                if (saved) {
                    this.items = JSON.parse(saved);
                }
            } catch (e) {
                this.items = [];
            }
        },

        save() {
            try {
                localStorage.setItem('perpus24_cart_books', JSON.stringify(this.items));
            } catch (e) {}
        },

        add(book) {
            if (!book || !book.id) return false;

            if (this.has(book.id)) {
                if (Alpine.store('toast')) {
                    Alpine.store('toast').notify(`'${book.title}' sudah ada di keranjang pinjam`, 'info');
                }
                this.isOpen = true;
                return false;
            }

            if (this.items.length >= this.maxItems) {
                if (Alpine.store('toast')) {
                    Alpine.store('toast').notify(`Maksimal ${this.maxItems} buku per peminjaman sesuai pedoman perpustakaan`, 'warning');
                }
                this.isOpen = true;
                return false;
            }

            if (book.available_stock !== undefined && book.available_stock <= 0) {
                if (Alpine.store('toast')) {
                    Alpine.store('toast').notify(`Stok buku '${book.title}' sedang habis`, 'error');
                }
                return false;
            }

            this.items.push({
                id: book.id,
                title: book.title,
                author: book.author || 'Anonim',
                isbn: book.isbn || '-',
                rack_location: book.rack_location || '-',
                cover_url: book.cover_url || null,
                category_name: book.category ? book.category.name : 'Umum',
                category_code: book.category ? book.category.code : '000',
                available_stock: book.available_stock || 1
            });

            this.save();

            if (Alpine.store('toast')) {
                Alpine.store('toast').notify(`Buku '${book.title}' dimasukkan ke keranjang`, 'success');
            }
            this.isOpen = true;
            return true;
        },

        remove(bookId) {
            this.items = this.items.filter(item => item.id !== bookId);
            this.save();
            if (Alpine.store('toast')) {
                Alpine.store('toast').notify('Buku dihapus dari keranjang', 'info');
            }
        },

        clear() {
            this.items = [];
            this.save();
            if (Alpine.store('toast')) {
                Alpine.store('toast').notify('Keranjang pinjam telah dikosongkan', 'info');
            }
        },

        has(bookId) {
            return this.items.some(item => item.id === bookId);
        },

        get count() {
            return this.items.length;
        },

        open() {
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        }
    });
});

// Lightweight Scroll-Reveal Controller using native IntersectionObserver
function initScrollObserver() {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.scroll-reveal').forEach(el => el.classList.add('revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                obs.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: '0px 0px -30px 0px',
        threshold: 0.05
    });

    document.querySelectorAll('.scroll-reveal:not(.revealed)').forEach(el => {
        observer.observe(el);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollObserver);
} else {
    initScrollObserver();
}

window.initScrollObserver = initScrollObserver;

Alpine.start();
