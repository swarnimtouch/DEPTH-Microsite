function showPopup(type, message) {
    const popup = document.getElementById("statusPopup");
    const text = document.getElementById("popupMessage");

    if (!popup || !text) {
        alert(message); // fallback
        return;
    }

    popup.className = `status-popup ${type}`;
    text.innerText = message;

    popup.style.display = "block";

    setTimeout(() => {
        popup.style.display = "none";
    }, 2000);
}

/* =========================================
   1. MODAL & LOGIN LOGIC
   ========================================= */

const loginModal = document.getElementById("loginModal");
const registerModal = document.getElementById("registerModal");

const loginBtn = document.getElementById("openLoginModal");
const registerBtn = document.getElementById("openRegisterModal");

const closeLoginBtn = document.querySelector(".close-modal-btn");
const closeRegisterBtn = document.querySelector(".close-register-btn");

const body = document.body;

function openLoginModal() {
    if (loginModal) {
        loginModal.style.display = "flex";
        body.style.overflow = "hidden";
    }
}

function closeLoginModal() {
    if (loginModal) {
        loginModal.style.display = "none";
        body.style.overflow = "auto";
    }
}

function openRegisterModal() {
    if (registerModal) {
        registerModal.style.display = "flex";
        body.style.overflow = "hidden";
    }
}

function closeRegisterModal() {
    if (registerModal) {
        registerModal.style.display = "none";
        body.style.overflow = "auto";
    }
}

if (loginBtn) {
    loginBtn.addEventListener("click", openLoginModal);
}

if (registerBtn) {
    registerBtn.addEventListener("click", openRegisterModal);
}

if (closeLoginBtn) {
    closeLoginBtn.addEventListener("click", closeLoginModal);
}

if (closeRegisterBtn) {
    closeRegisterBtn.addEventListener("click", closeRegisterModal);
}

window.addEventListener("click", function (event) {
    if (event.target === loginModal) {
        closeLoginModal();
    }
    if (event.target === registerModal) {
        closeRegisterModal();
    }
});


/* =========================================
   2. SLIDER LOGIC (OPTIMIZED & STABLE)
   ========================================= */
$(document).ready(function() {

    // --- VARIABLES ---
    const $track = $("#sliderTrack");
    const $heroBanner = $(".hero-banner");
    const sliderData = window.sliderData || [];
    let slideIndex = 0;
    let slideInterval;
    const slideDuration = 3000; // ✅ 3 Seconds Timer

    // Agar track ya data nahi hai to return
    if ($track.length === 0 || sliderData.length === 0) return;

    // --- 1. INITIALIZATION ---
    function initSlider() {
        $track.empty(); // Purana content clear

        // Slides create karein
        $.each(sliderData, function(index, item) {
            let mediaElement = '';

            if (item.type === 'image') {
                // Image
                mediaElement = `<img src="${item.src}" alt="Event Banner">`;
            } else if (item.type === 'video') {
                // Video (Muted, PlaysInline important for autoplay)
                mediaElement = `
                    <video poster="${item.poster || ''}" muted playsinline loop>
                        <source src="${item.src}" type="video/mp4">
                    </video>`;
            }

            const slideHtml = `<div class="slide">${mediaElement}</div>`;
            $track.append(slideHtml);
        });

        // Start Slider
        updateSlider();
        startAutoSlide();
    }

    // --- 2. UPDATE SLIDER (Movement & Background) ---
    function updateSlider() {
        // A. Track Move karein
        $track.css("transform", `translateX(-${slideIndex * 100}%)`);

        // B. Background Blur Effect Update karein
        updateBackground(slideIndex);

        // C. Video Handling (Current video play, baaki pause)
        const $allVideos = $(".slide video");
        $allVideos.each(function() {
            $(this).get(0).pause();
            $(this).get(0).currentTime = 0;
        });

        const $currentSlide = $(".slide").eq(slideIndex);
        const $activeVideo = $currentSlide.find("video");

        if ($activeVideo.length > 0) {
            // Video hai to play karein
            const playPromise = $activeVideo.get(0).play();
            if (playPromise !== undefined) {
                playPromise.catch(error => console.log("Auto-play blocked:", error));
            }
        }
    }

    // --- 3. BACKGROUND UPDATER (Blur Effect) ---
    function updateBackground(index) {
    const data = sliderData[index];
    
    // Background Elements select karein
    const $bgImage = $("#bgImage");
    const $bgVideo = $("#bgVideo");

    // Reset Active Classes (Dono ko chupao pehle)
    $(".bg-media").removeClass("active");

    if (data.type === 'image') {
        // --- Agar IMAGE hai ---
        $bgVideo.trigger('pause'); // Video pause karo (CPU save karne ke liye)
        
        $bgImage.attr("src", data.src); // Image set karo
        $bgImage.addClass("active");    // Image dikhao

    } else if (data.type === 'video') {
        // --- Agar VIDEO hai ---
        $bgVideo.attr("src", data.src); // Video source set karo
        $bgVideo.addClass("active");    // Video dikhao
        
        // Background video play karo
        const videoEl = $bgVideo.get(0);
        videoEl.load();
        const playPromise = videoEl.play();
        if (playPromise !== undefined) {
            playPromise.catch(error => console.log("Bg Video Auto-play blocked:", error));
        }
    }
}

    // --- 4. NEXT SLIDE ---
    function nextSlide() {
        slideIndex++;
        if (slideIndex >= sliderData.length) {
            slideIndex = 0;
        }
        updateSlider();
    }

    // --- 5. TIMER CONTROL ---
    function startAutoSlide() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, slideDuration);
    }

    // Init Call
    initSlider();
});
