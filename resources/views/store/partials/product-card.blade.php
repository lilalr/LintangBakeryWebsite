<article
    class="product-card lb-product-card"
    onclick="if (!event.target.closest('form') && !event.target.closest('button') && !event.target.closest('a')) window.location='{{ route('store.product', $product) }}';"
    style="cursor: pointer;"
>
    {{-- PRODUCT IMAGE --}}
    <a
        href="{{ route('store.product', $product) }}"
        class="product-image lb-product-image"
    >
        <img
            src="{{ $product->image ?: '/images/products/kueTambang.jpg' }}"
            alt="{{ $product->name }}"
            loading="lazy"
        >

        <span class="product-tag lb-product-tag">
            {{ $product->category }}
        </span>
    </a>

    {{-- PRODUCT INFORMATION --}}
    <div class="product-card-body lb-product-body">

        <div class="lb-product-meta">
            <span class="lb-minimum-order">
                Min. Mix 50 Bal
            </span>

            <span class="lb-product-type">
                Grosir Reseller
            </span>
        </div>

        <h3 class="lb-product-title">
            <a href="{{ route('store.product', $product) }}">
                {{ $product->name }}
            </a>
        </h3>

        <p class="lb-product-description">
            {{ \Illuminate\Support\Str::limit($product->description, 90) }}
        </p>

        {{-- PRICE AND CART --}}
        <div class="product-card-foot lb-product-footer">

            <div class="lb-price">
                <span class="lb-price-label">Harga grosir</span>

                <strong>
                    Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}
                    <small>/ bal</small>
                </strong>
            </div>

            <form
                method="POST"
                action="{{ route('cart.store', $product) }}"
                onclick="event.stopPropagation();"
            >
                @csrf
<<<<<<< Updated upstream
                <input type="hidden" name="quantity" value="50">
                <button class="icon-button" aria-label="Tambah 50 bal ke keranjang" title="Tambah 50 bal ke keranjang">+</button>
=======

                <input
                    type="hidden"
                    name="quantity"
                    value="50"
                >

                <button
                    class="icon-button lb-add-cart"
                    type="submit"
                    aria-label="Tambah 50 bal ke keranjang"
                    title="Tambah 50 bal ke keranjang"
                >
                    <svg
                        width="19"
                        height="19"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/>
                        <circle cx="10" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M14 9v5"/>
                        <path d="M11.5 11.5h5"/>
                    </svg>
                </button>
>>>>>>> Stashed changes
            </form>

        </div>
    </div>
</article>

<style>
    /* PRODUCT CARD LINTANG BAKERY */

    .lb-product-card {
        background: #fff !important;
        border: 1px solid #eadfd4 !important;
        border-radius: 15px !important;
        overflow: hidden;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        box-shadow: 0 3px 12px rgba(57, 37, 27, .035);
    }

    .lb-product-card:hover {
        transform: translateY(-5px);
        border-color: #d7b99f !important;
        box-shadow: 0 12px 28px rgba(57, 37, 27, .10);
    }

    .lb-product-image {
        position: relative;
        display: block;
        height: 205px;
        overflow: hidden;
        background: #f8f1e9;
    }

    .lb-product-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .45s ease;
    }

    .lb-product-card:hover .lb-product-image img {
        transform: scale(1.055);
    }

    .lb-product-tag {
        position: absolute;
        left: 13px;
        top: 13px;
        background: rgba(255, 255, 255, .95) !important;
        color: #70452d !important;
        border: 1px solid #eadfd4;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 750;
        backdrop-filter: blur(6px);
    }

    .lb-product-body {
        padding: 17px !important;
    }

    .lb-product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
    }

    .lb-minimum-order {
        padding: 5px 8px;
        border-radius: 5px;
        background: #f8eee4;
        border: 1px solid #ead7c4;
        color: #805333;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .lb-product-type {
        color: #9a8a7e;
        font-size: 10px;
        white-space: nowrap;
    }

    .lb-product-title {
        margin: 0 0 8px !important;
        font-family: Georgia, serif;
        font-size: 18px !important;
        line-height: 1.4;
        font-weight: 700;
        color: #39251b;
    }

    .lb-product-title a {
        color: inherit;
        text-decoration: none;
        transition: color .2s ease;
    }

    .lb-product-title a:hover {
        color: #a66e46;
    }

    .lb-product-description {
        min-height: 43px;
        margin: 0 0 17px !important;
        color: #87776b !important;
        font-size: 12px !important;
        line-height: 1.75;
    }

    .lb-product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-top: 13px;
        border-top: 1px solid #f0e8e0;
    }

    .lb-price {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .lb-price-label {
        color: #a18f81;
        font-size: 10px;
    }

    .lb-price strong {
        color: #70452d;
        font-size: 16px;
        font-weight: 800;
        white-space: nowrap;
    }

    .lb-price strong small {
        color: #9b8a7d;
        font-size: 10px;
        font-weight: 500;
    }

    .lb-add-cart {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        flex-shrink: 0;
        border: none !important;
        border-radius: 10px !important;
        background: #70452d !important;
        color: #fff !important;
        transition: background .2s ease, transform .2s ease;
    }

    .lb-add-cart:hover {
        background: #39251b !important;
        transform: scale(1.06);
    }

    @media (max-width: 600px) {
        .lb-product-image {
            height: 175px;
        }

        .lb-product-body {
            padding: 13px !important;
        }

        .lb-product-title {
            font-size: 16px !important;
        }

        .lb-product-meta {
            flex-wrap: wrap;
        }

        .lb-price strong {
            font-size: 14px;
        }
    }
</style>
