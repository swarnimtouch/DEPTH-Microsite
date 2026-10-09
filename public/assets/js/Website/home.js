document.addEventListener("DOMContentLoaded", () => {
  const tabLinks = document.querySelectorAll(".tab-link");
  const tabContents = document.querySelectorAll(".tab-content");

  tabLinks.forEach((link) => {
    link.addEventListener("click", () => {
      const tabId = link.getAttribute("data-tab");

      tabLinks.forEach((btn) => btn.classList.remove("active"));

      link.classList.add("active");

      tabContents.forEach((content) => {
        content.style.display = "none";
      });

      const activeTab = document.getElementById(tabId);
      if (activeTab) {
        activeTab.style.display = "block";
      }

      const chatInputArea = document.querySelector(".chat-input-area");

      if (chatInputArea) {
        if (tabId === "polls" || tabId === "feedback") {
          chatInputArea.style.display = "none";
        } else {
          chatInputArea.style.display = "block";
        }
      }
    });
  });

  const pollOptions = document.querySelectorAll(".poll-option");
  const votedSpanHTML = '<span><i class="fa-solid fa-check"></i> Voted</span>';

  pollOptions.forEach((clickedOption) => {
    clickedOption.addEventListener("click", () => {
      pollOptions.forEach((option) => {
        option.classList.remove("selected");
        const votedSpan = option.querySelector("span");
        if (votedSpan) {
          votedSpan.remove();
        }
      });

      clickedOption.classList.add("selected");
      clickedOption.insertAdjacentHTML("beforeend", votedSpanHTML);
    });
  });

  function initMobileChatButton() {
    const mobileChatBtn = document.getElementById("mobileChatBtn");
    const chatSidebar = document.querySelector(".chat-sidebar");

    if (mobileChatBtn && chatSidebar) {
      mobileChatBtn.addEventListener("click", () => {
        chatSidebar.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });

        const chatTab = document.querySelector('.tab-link[data-tab="chat"]');
        if (chatTab) {
          chatTab.click();
        }

        mobileChatBtn.style.transform = "scale(0.95)";
        setTimeout(() => {
          mobileChatBtn.style.transform = "";
        }, 150);
      });
    }
  }

  function initMobileScrolling() {
    if (window.innerWidth > 767) return;

    const videoSection = document.querySelector(".video-section");
    const chatSidebar = document.querySelector(".chat-sidebar");
    const chatScrollable = document.querySelector(
      ".sidebar-scrollable-content"
    );

    if (!videoSection || !chatSidebar || !chatScrollable) return;

    let isScrolling = false;
    let scrollTimer = null;
    let isChatSidebarVisible = false;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          isChatSidebarVisible = entry.isIntersecting;
        });
      },
      {
        threshold: 1,
      }
    );

    observer.observe(chatSidebar);

    function isVideoSectionAtBottom() {
      return (
        videoSection.scrollHeight - videoSection.scrollTop <=
        videoSection.clientHeight + 1
      );
    }

    function isChatSidebarAtTop() {
      return chatScrollable.scrollTop === 0;
    }

    function isChatSidebarAtBottom() {
      return (
        chatScrollable.scrollHeight - chatScrollable.scrollTop <=
        chatScrollable.clientHeight + 1
      );
    }

    function isVideoSectionAtTop() {
      return videoSection.scrollTop === 0;
    }

    function handleVideoSectionScroll(event) {
      if (isScrolling) return;

      if (isVideoSectionAtBottom() && event.deltaY > 0) {
        if (isChatSidebarVisible) {
          isScrolling = true;

          chatScrollable.scrollTop += event.deltaY * 2;

          clearTimeout(scrollTimer);
          scrollTimer = setTimeout(() => {
            isScrolling = false;
          }, 50);

          event.preventDefault();
        }
      } else if (isVideoSectionAtTop() && event.deltaY < 0) {
        return true;
      }
    }

    function handleChatSidebarScroll(event) {
      if (isScrolling) return;

      if (isChatSidebarAtTop() && event.deltaY < 0) {
        isScrolling = true;

        videoSection.scrollTop += event.deltaY * 2;

        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
          isScrolling = false;
        }, 50);

        event.preventDefault();
      } else if (isChatSidebarAtBottom() && event.deltaY > 0) {
        return true;
      }
    }

    videoSection.addEventListener("wheel", handleVideoSectionScroll, {
      passive: false,
    });
    chatScrollable.addEventListener("wheel", handleChatSidebarScroll, {
      passive: false,
    });

    let startY = 0;
    let currentElement = null;

    videoSection.addEventListener(
      "touchstart",
      (e) => {
        startY = e.touches[0].clientY;
        currentElement = videoSection;
      },
      { passive: true }
    );

    videoSection.addEventListener(
      "touchmove",
      (e) => {
        if (isScrolling) return;

        const currentY = e.touches[0].clientY;
        const deltaY = startY - currentY;

        if (isVideoSectionAtBottom() && deltaY < -5) {
          if (isChatSidebarVisible) {
            isScrolling = true;

            chatScrollable.scrollTop -= deltaY * 0.5;

            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
              isScrolling = false;
            }, 50);

            e.preventDefault();
          }
        }

        startY = currentY;
      },
      { passive: false }
    );

    chatScrollable.addEventListener(
      "touchstart",
      (e) => {
        startY = e.touches[0].clientY;
        currentElement = chatScrollable;
      },
      { passive: true }
    );

    chatScrollable.addEventListener(
      "touchmove",
      (e) => {
        if (isScrolling) return;

        const currentY = e.touches[0].clientY;
        const deltaY = startY - currentY;

        if (isChatSidebarAtTop() && deltaY > 5) {
          isScrolling = true;

          videoSection.scrollTop -= deltaY * 0.5;

          clearTimeout(scrollTimer);
          scrollTimer = setTimeout(() => {
            isScrolling = false;
          }, 50);

          e.preventDefault();
        }

        startY = currentY;
      },
      { passive: false }
    );
  }

  function initScrollToBottom() {
    const bottomBtn = document.getElementById("scrollToBottomBtn");

    if (!bottomBtn) return;

    bottomBtn.addEventListener("click", () => {
      window.scrollTo({
        top: document.documentElement.scrollHeight,
        behavior: "smooth",
      });
    });

    window.addEventListener("scroll", () => {
      const scrollPosition = window.scrollY + window.innerHeight;

      const totalHeight = document.documentElement.scrollHeight;

      if (scrollPosition >= totalHeight - 100) {
        bottomBtn.classList.add("btn-hidden");
      } else {
        bottomBtn.classList.remove("btn-hidden");
      }
    });
  }

  function initDesktopScrollIsolation() {
    if (window.innerWidth <= 767) return;

    const videoSection = document.querySelector(".video-section");
    const chatScrollable = document.querySelector(
      ".sidebar-scrollable-content"
    );

    if (!videoSection || !chatScrollable) return;

    videoSection.addEventListener("wheel", (e) => {
      if (videoSection.scrollHeight > videoSection.clientHeight) {
        const isAtTop = videoSection.scrollTop === 0;
        const isAtBottom =
          videoSection.scrollTop + videoSection.clientHeight >=
          videoSection.scrollHeight - 1;

        if ((isAtTop && e.deltaY < 0) || (isAtBottom && e.deltaY > 0)) {
          return true;
        }

        e.stopPropagation();
      }
    });

    chatScrollable.addEventListener("wheel", (e) => {
      if (chatScrollable.scrollHeight > chatScrollable.clientHeight) {
        const isAtTop = chatScrollable.scrollTop === 0;
        const isAtBottom =
          chatScrollable.scrollTop + chatScrollable.clientHeight >=
          chatScrollable.scrollHeight - 1;

        if ((isAtTop && e.deltaY < 0) || (isAtBottom && e.deltaY > 0)) {
          return true;
        }

        e.stopPropagation();
      }
    });
  }

  function initializeScrollFeatures() {
    if (window.innerWidth <= 767) {
      initMobileScrolling();
    } else {
      initDesktopScrollIsolation();
    }
  }

  function handleResize() {
    initializeScrollFeatures();
  }

  function initSmoothScrolling() {
    const internalLinks = document.querySelectorAll('a[href^="#"]');

    internalLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();

        const targetId = link.getAttribute("href");
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      });
    });
  }

  function initFollowButton() {
    const followBtn = document.querySelector(".follow-btn");
    if (followBtn) {
      followBtn.addEventListener("click", () => {
        const isFollowing = followBtn.innerHTML.includes("Following");

        if (isFollowing) {
          followBtn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Follow';
          followBtn.style.backgroundColor = "";
        } else {
          followBtn.innerHTML =
            '<i class="fa-solid fa-user-check"></i> Following';
          followBtn.style.backgroundColor = "#22c55e";
        }
      });
    }
  }

  function initActionButtons() {
    const likeBtn = document.querySelector(".action-btn:nth-child(1)");
    const commentBtn = document.querySelector(".action-btn:nth-child(2)");

    if (likeBtn) {
      likeBtn.addEventListener("click", () => {
        const currentText = likeBtn.innerHTML;
        const likeCount = parseInt(currentText.match(/\d+/)?.[0]) || 1234;

        if (currentText.includes("fa-thumbs-up")) {
          likeBtn.innerHTML = `<i class="fa-solid fa-thumbs-up" style="color: #3b82f6;"></i> ${
            likeCount + 1
          }`;
        } else {
          likeBtn.innerHTML = `<i class="fa-solid fa-thumbs-up"></i> ${
            likeCount - 1
          }`;
        }
      });
    }

    if (commentBtn) {
      commentBtn.addEventListener("click", () => {
        const qaTab = document.querySelector('.tab-link[data-tab="qa"]');
        if (qaTab) {
          qaTab.click();

          if (window.innerWidth <= 767) {
            const chatSidebar = document.querySelector(".chat-sidebar");
            if (chatSidebar) {
              chatSidebar.scrollIntoView({
                behavior: "smooth",
                block: "start",
              });
            }
          }
        }
      });
    }
  }

  function initQuickActions() {
    const actionBoxes = document.querySelectorAll(".action-box");

    actionBoxes.forEach((box) => {
      box.addEventListener("click", () => {
        const actionText = box.querySelector("span").textContent;

        box.style.backgroundColor = "var(--bg-light-hover)";
        box.style.borderColor = "var(--accent-blue)";

        setTimeout(() => {
          box.style.backgroundColor = "";
          box.style.borderColor = "";
        }, 300);

        console.log(`Action clicked: ${actionText}`);

        if (actionText === "Raise Hand") {
          const participantIcon = document.querySelector(
            ".participant-icon.raised"
          );
          if (participantIcon) {
            participantIcon.style.color =
              participantIcon.style.color === "var(--text-secondary)"
                ? "#eab308"
                : "var(--text-secondary)";
          }
        }
      });
    });
  }

  function initDownloadButtons() {
    const downloadButtons = document.querySelectorAll(
      ".card-link, .action-btn.download"
    );

    downloadButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();

        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-check"></i> Downloading...';
        button.style.color = "#22c55e";

        setTimeout(() => {
          button.innerHTML = originalText;
          button.style.color = "";
        }, 2000);

        console.log("Download initiated");
      });
    });
  }

  function initializeAllFeatures() {
    initializeScrollFeatures();
    initSmoothScrolling();
    initMobileChatButton();
    initFollowButton();
    initActionButtons();
    initQuickActions();
    initDownloadButtons();
    initScrollToBottom();
    initFeedbackSystem();
  }

  initializeAllFeatures();

  window.addEventListener("resize", handleResize);

  window.addEventListener("beforeunload", () => {
    window.removeEventListener("resize", handleResize);
  });
});

function initFeedbackSystem() {
  const stars = document.querySelectorAll(".star-rating-widget .star");
  const ratingText = document.querySelector(".rating-text");
  const submitBtn = document.getElementById("submitFeedbackBtn");
  const feedbackContainer = document.querySelector(".feedback-container");
  const successMessage = document.querySelector(".feedback-success");
  let currentRating = 0;

  const messages = ["Poor", "Fair", "Good", "Very Good", "Excellent"];

  stars.forEach((star) => {
    star.addEventListener("click", () => {
      currentRating = parseInt(star.getAttribute("data-value"));

      stars.forEach((s) => {
        const value = parseInt(s.getAttribute("data-value"));
        if (value <= currentRating) {
          s.classList.add("active");
        } else {
          s.classList.remove("active");
        }
      });

      if (currentRating > 0) {
        ratingText.textContent = messages[currentRating - 1];
        ratingText.style.color = "var(--text-primary)";
      }
    });
  });

  if (submitBtn) {
    submitBtn.addEventListener("click", () => {
      const textVal = document.getElementById("feedbackText").value;

      if (currentRating === 0) {
        alert("Please select a star rating first.");
        return;
      }

      feedbackContainer.style.display = "none";
      successMessage.style.display = "block";

      console.log(
        `Feedback Submitted: Rating ${currentRating}, Text: ${textVal}`
      );
    });
  }
}

function isMobileDevice() {
  return window.innerWidth <= 767;
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

if (typeof module !== "undefined" && module.exports) {
  module.exports = { isMobileDevice };
}

console.log("LiveStream Pro initialized successfully!");
