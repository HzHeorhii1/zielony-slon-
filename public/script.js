document.addEventListener("DOMContentLoaded", function () {
    const languageSwitchers = document.querySelectorAll(".lang");
    const favButton = document.querySelector(".favorite-btn");
    const favLinksContainer = document.querySelector(".fav-links");
    const shareBtn = document.querySelector(".share-btn");
    const dropdownFilterHeader = document.querySelector(
        ".dropdown-filter-header",
    );
    const dropdownFilter = document.querySelector(".dropdown-filter");
    const resetFilterBtn = document.querySelectorAll(".reset-btn");
    const searchBtn = document.querySelectorAll(".search-btn");
    const filterInputs = document.querySelectorAll(".filter-container input");
    const calendarEl = document.getElementById("calendar");
    const dropdownBtn = document.querySelector(".dropdown-btn");
    const dropdownText = document.querySelector(".dropdown-text");
    const dropdownMenu = document.querySelector(".dropdown-menu");
    const options = document.querySelectorAll(".dropdown-option");
    const legendContainer = document.querySelector(".legend");
    const counterContainer = document.querySelector(".lesson-counter");

    let favoriteLinks = getFavoriteLinks() || [];
    let currentDate = new Date();
    let currentView = "timeGridWeek";
    let currentScheduleData = null;
    let suggestionTimeouts = {};
    let calendar;

    const lessonTypeColors = {
        laboratorium: "#1A8238",
        wykład: "#247C84",
        projekt: "#555500",
        egzamin: "#007BB0",
        audytoryjne: "#007BB0",
        rektorskie: "#1A8238",
        lektorat: "#C44F00",
    };

    function updateCalendarView(view) {
        if (calendar) {
            calendar.changeView(view);
        }
    }

    function initializeCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: currentView,
            firstDay: 1,
            locale: "pl",
            buttonText: {
                today: "Dzisiaj",
            },
            headerToolbar: {
                left: "today prev,next",
                center: "title",
                right: "customViewSelector",
            },
            slotMinTime: "07:00:00",
            slotMaxTime: "21:00:00",
            allDaySlot: false,
            slotLabelFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            },
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            },
            events: fetchEventsForCalendar,
            eventColor: "#3788d8",
            eventClick: function (info) {
                alert(
                    `Zajęcie: ${info.event.title}\nCzas: ${info.event.start.toLocaleString()}`,
                );
            },
            datesSet: function (info) {
                currentDate = new Date(info.view.currentStart);
                fetchSchedule();
                removeExtraButton();
            },
            viewDidMount: function (info) {
                currentView = info.view.type;
                updateDropdownText(currentView);
                removeExtraButton()
            },
        });
        calendar.render();
    }

    function updateDropdownText(view) {
        const viewMapping = {
            timeGridDay: "Dzień",
            timeGridWeek: "Tydzień",
            dayGridMonth: "Miesiąc",
        };
        if (viewMapping[view]) {
            dropdownText.textContent = viewMapping[view];
        }
    }

    function insertCustomButton() {
        const placeholder = document.querySelector(".fc-customViewSelector-button");
        if (placeholder) {
            placeholder.replaceWith(dropdownBtn);
        }
    }

    setTimeout(insertCustomButton);

    const toolbarObserver = new MutationObserver(() => {
        setTimeout(removeExtraButton, 50);
    });

    const toolbar = document.querySelector(".fc-header-toolbar");
    if (toolbar) {
        toolbarObserver.observe(toolbar, { childList: true, subtree: true });
    }

    dropdownBtn.addEventListener("click", function (event) {
        event.stopPropagation();
        const rect = dropdownBtn.getBoundingClientRect();
        dropdownMenu.style.position = "fixed";
        dropdownMenu.style.width = `${dropdownBtn.offsetWidth}px`;
        dropdownMenu.style.left = `${rect.left}px`;
        dropdownMenu.style.top = `${rect.bottom}px`;

        dropdownMenu.classList.toggle("active");
    });

    for (const option of options) {
        option.addEventListener("click", function (event) {
            event.stopPropagation();
            const selectedView = option.getAttribute("data-view");

            dropdownMenu.classList.remove("active");
            dropdownBtn.classList.remove("active");
            updateCalendarView(selectedView);
            // updateDropdownText(selectedView)
            removeExtraButton();
        });
    }

    document.addEventListener("click", function (event) {
        if (
            !dropdownBtn.contains(event.target) &&
            !dropdownMenu.contains(event.target)
        ) {
            dropdownMenu.classList.remove("active");
            dropdownBtn.classList.remove("active");
        }
    });

    function removeExtraButton() {
        setTimeout(() => {
            document.querySelectorAll(".fc-customViewSelector-button").forEach((button) => button.remove());
        });
    }

    for (const lang of languageSwitchers) { // delete
        lang.addEventListener("click", function () {
            languageSwitchers.forEach((el) => el.classList.remove("active"));
            this.classList.add("active");
            const selectedLang = this.getAttribute("data-lang");
            console.log(`Selected Language ${selectedLang}`);
        });
    }


    shareBtn.addEventListener("click", function () {
        navigator.clipboard
            .writeText(window.location.href)
            .then(() => console.log("Text copied to clipboard"))
            .catch((err) => console.error("Could not copy text: ", err));
    });

    favButton.addEventListener("click", () => {
        renderFavoriteLinks();
    });

    function renderFavoriteLinks() {
        favLinksContainer.innerHTML = "";
        for (const link of favoriteLinks) {
            const favLink = document.createElement("a");
            favLink.href = link;
            favLink.textContent = link;
            favLinksContainer.appendChild(favLink);
        }
    }

    favButton.addEventListener("click", () => {
        const currentUrl = window.location.href;
        if (favoriteLinks.length >= 10) {
            favoriteLinks.shift();
        }
        if (!favoriteLinks.includes(currentUrl)) favoriteLinks.push(currentUrl);

        setFavoriteLinks(favoriteLinks);
        renderFavoriteLinks();

        console.log(`Link added: ${currentUrl}`);
    });

    function setFavoriteLinks(links) {
        localStorage.setItem("favoriteLinks", JSON.stringify(links));
    }

    function getFavoriteLinks() {
        return JSON.parse(localStorage.getItem("favoriteLinks"));
    }

    for(const el of resetFilterBtn){
        el.addEventListener("click", () => {
            const filterInputs = document.querySelectorAll(".filter-container input");
            for(const input of filterInputs){
                input.value = "";
            }
            const filterCheckboxes = document.querySelectorAll(
                '.dropdown-content input[type="checkbox"]',
            );
            for(const checkbox of filterCheckboxes){
                checkbox.checked = false
            }

            fetchSchedule();
            hideAllSuggestions();
        });
    }


    dropdownFilterHeader.addEventListener("click", function () {
        dropdownFilter.classList.toggle("active");
        dropdownFilterHeader.classList.toggle("active");
    });

    function changeDate(direction) {
        if (currentView === "timeGridDay") {
            currentDate.setDate(currentDate.getDate() + direction);
        }
        if (currentView === "timeGridWeek") {
            currentDate.setDate(currentDate.getDate() + 7 * direction);
        }
        if (currentView === "dayGridMonth") {
            currentDate.setMonth(currentDate.getMonth() + direction);
        }
    }

    function fetchEventsForCalendar(fetchInfo, successCallback, failureCallback) {
        let startDate = new Date(currentDate);
        let endDate = new Date(startDate);

        if (currentView === "timeGridWeek") {
            startDate.setDate(startDate.getDate() - startDate.getDay() + 1);
            endDate.setDate(startDate.getDate() + 7);
        } else if (currentView === "timeGridDay") {
            endDate.setDate(startDate.getDate() + 1);
        } else if (currentView === "dayGridMonth") {
            startDate.setDate(1);
            endDate.setMonth(startDate.getMonth() + 1);
            endDate.setDate(1);
        }

        const formattedStartDate = `${startDate.getFullYear()}-${String(startDate.getMonth() + 1).padStart(2, "0")}-${String(startDate.getDate()).padStart(2, "0")}T00:00:00+02:00`;
        const formattedEndDate = `${endDate.getFullYear()}-${String(endDate.getMonth() + 1).padStart(2, "0")}-${String(endDate.getDate()).padStart(2, "0")}T00:00:00+02:00`;
        const teacher = document.querySelector('input[name="teacher"]').value;
        const room = document.querySelector('input[name="room"]').value;
        const group = document.querySelector('input[name="group"]').value;
        const subject = document.querySelector('input[name="subject"]').value;
        const albumNumber = Number.parseInt(
            document.querySelector('input[name="albumNumber"]').value,
            10,
        );
        const filters = Array.from(
            document.querySelectorAll(
                '.dropdown-content input[type="checkbox"]:checked',
            ),
        ).map((checkbox) => checkbox.value);
        const params = new URLSearchParams();
        params.append("start", formattedStartDate);
        params.append("end", formattedEndDate);
        if (teacher) {
            params.append("teacher", teacher);
        }
        if (room) {
            params.append("room", room);
        }
        if (group) {
            params.append("group", group);
        }
        if (subject) {
            params.append("kind", "subject");
            params.append("query", subject);
        }
        if (albumNumber) {
            params.append("id", albumNumber);
        }
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        history.pushState({}, "", newUrl);
        let apiUrl = `/api/user/schedule?${params.toString()}`;

        fetch(apiUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                let events = [];
                let usedLessonTypes = new Set();
                let lessonTypeCounts = {};
                if (data) {
                    for(const date of Object.keys(data)){
                        if (Array.isArray(data[date])) {
                            for (const scheduleItem of data[date]) {
                                let eventText = ``;
                                let eventColor = "#3788d8";
                                let lessonForm = scheduleItem.lesson_form
                                    ? scheduleItem.lesson_form.toLowerCase()
                                    : "";

                                if (scheduleItem.subject) {
                                    eventText = ` ${scheduleItem.subject.title} `;
                                } else {
                                    eventText = `${scheduleItem.title} `;
                                }
                                if (scheduleItem.color) {
                                    eventColor = scheduleItem.color;
                                }
                                const lessonFormLower = scheduleItem.lesson_form
                                    ? scheduleItem.lesson_form.toLowerCase()
                                    : null;

                                if (
                                    filters.length === 0 ||
                                    filters.some((filter) => {
                                        if (
                                            filter === "laboratoria" &&
                                            lessonFormLower === "laboratorium"
                                        ) {
                                            return true;
                                        } else if (
                                            filter === "wykłady" &&
                                            lessonFormLower === "wykład"
                                        ) {
                                            return true;
                                        } else if (
                                            filter === "audytoria" &&
                                            lessonFormLower === "audytoryjne"
                                        ) {
                                            return true;
                                        } else if (
                                            filter === "lektoraty" &&
                                            lessonFormLower === "lektorat"
                                        ) {
                                            return true;
                                        }
                                        return false;
                                    })
                                ) {
                                    events.push({
                                        title: eventText,
                                        start: scheduleItem.startTime,
                                        end: scheduleItem.endTime,
                                        color: eventColor,
                                        extendedProps: {
                                            ...scheduleItem,
                                        },
                                    });
                                    if (lessonFormLower) {
                                        usedLessonTypes.add(lessonFormLower);
                                        lessonTypeCounts[lessonFormLower] =
                                            (lessonTypeCounts[lessonFormLower] || 0) + 1;
                                    }
                                }
                            }
                        }
                    }

                    updateLegend(usedLessonTypes);
                    updateCounter(lessonTypeCounts);
                    successCallback(events);
                }
                currentScheduleData = data;
                console.log("Schedule data:", currentScheduleData);
            })
            .catch((error) => {
                console.error("Error fetching schedule:", error);
                failureCallback(error);
            });
    }
    function updateCounter(lessonTypeCounts) {
        counterContainer.innerHTML = "";
        let totalLessons = 0;
        for (const lessonType in lessonTypeCounts) {
            if (lessonTypeCounts.hasOwnProperty(lessonType)) {
                const count = lessonTypeCounts[lessonType];
                totalLessons += count;
                const counterItem = document.createElement("div");
                counterItem.classList.add("counter-item");
                const label = document.createElement("span");
                label.textContent = `${lessonType.charAt(0).toUpperCase() + lessonType.slice(1)}: ${count}`;
                counterItem.appendChild(label);
                counterContainer.appendChild(counterItem);
            }
        }

        const totalLessonsItem = document.createElement("div");
        totalLessonsItem.textContent = `Razem: ${totalLessons}`;
        counterContainer.appendChild(totalLessonsItem);

    }


    function updateLegend(usedLessonTypes) {
        legendContainer.innerHTML = "";
        for (const lessonType of usedLessonTypes) {
            if (lessonTypeColors[lessonType]) {
                const legendItem = document.createElement("div");
                legendItem.classList.add("legend-item");

                const colorBox = document.createElement("span");
                colorBox.classList.add("lesson-type-legend");
                colorBox.style.backgroundColor = lessonTypeColors[lessonType];
                legendItem.appendChild(colorBox);

                const label = document.createElement("span");
                label.textContent =
                    lessonType.charAt(0).toUpperCase() + lessonType.slice(1);
                legendItem.appendChild(label);
                legendContainer.appendChild(legendItem);
            }
        }
    }


    function fetchSchedule() {
        if (calendar) calendar.refetchEvents();
    }

    function fetchSuggestions(filterType, query, inputElement) {
        const suggestionsContainer =
            inputElement.parentElement.querySelector(".suggestions");
        if (!query || query.length < 3) {
            if (suggestionsContainer) {
                suggestionsContainer.innerHTML = "";
            }
            return;
        }

        const url = `/api/suggestions/${filterType}?query=${encodeURIComponent(query)}`;
        fetch(url)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (suggestionsContainer) {
                    suggestionsContainer.innerHTML = "";
                } else {
                    const suggestionsDiv = document.createElement("div");
                    suggestionsDiv.classList.add("suggestions");
                    suggestionsDiv.style.position = "absolute";
                    suggestionsDiv.style.top = `${inputElement.offsetTop + inputElement.offsetHeight}px`;
                    suggestionsDiv.style.left = `${inputElement.offsetLeft}px`;
                    suggestionsDiv.style.width = `${inputElement.offsetWidth}px`;
                    inputElement.parentElement.appendChild(suggestionsDiv);
                }
                const list = document.createElement("ul");
                if (data) {
                    for (const item of data) {
                        let itemName = null;
                        if (item && typeof item === "object") {
                            itemName = item.item;
                        } else if (item && typeof item === "string") {
                            itemName = item;
                        }
                        if (itemName) {
                            const listItem = document.createElement("li");
                            listItem.textContent = itemName;
                            listItem.addEventListener("click", () => {
                                inputElement.value = itemName;
                                fetchSchedule();
                                inputElement.focus();
                                const suggestionsContainer =
                                    inputElement.parentElement.querySelector(".suggestions");
                                if (suggestionsContainer) {
                                    suggestionsContainer.innerHTML = "";
                                }
                            });
                            list.appendChild(listItem);
                        }
                    }
                    if (suggestionsContainer) {
                        suggestionsContainer.appendChild(list);
                    }
                }else {
                    const listItem = document.createElement("li");
                    listItem.textContent = `No Results for '${query}'`;
                    list.appendChild(listItem);
                    if (suggestionsContainer) {
                        suggestionsContainer.appendChild(list);
                    }
                }

                console.log("Sugestions:", data);
            })
            .catch((error) => {
                console.error("Error fetching suggestions:", error);
                if (suggestionsContainer) {
                    suggestionsContainer.innerHTML = "";
                }
            });
    }


    for (const input of filterInputs) {
        input.addEventListener("input", function () {
            const filterType = this.getAttribute("name");
            if (
                filterType === "teacher" ||
                filterType === "room" ||
                filterType === "subject" ||
                filterType === "group"
            ) {
                const query = this.value;
                clearTimeout(suggestionTimeouts[filterType]);
                const inputElement = this;
                suggestionTimeouts[filterType] = setTimeout(() => {
                    fetchSuggestions(filterType, query, inputElement);
                }, 300);
            }
        });
        input.addEventListener("focus", function () {
            const filterType = this.getAttribute("name");
            if (
                filterType === "teacher" ||
                filterType === "room" ||
                filterType === "subject" ||
                filterType === "group"
            ) {
                const query = this.value;
                fetchSuggestions(filterType, query, this);
            }
        });
        input.addEventListener("blur", function (e) {
            const suggestionsContainer =
                this.parentElement.querySelector(".suggestions");
            if (
                suggestionsContainer &&
                !suggestionsContainer.contains(e.relatedTarget) &&
                e.relatedTarget !== this
            ) {
                setTimeout(() => {
                    hideAllSuggestions();
                }, 100);
            }
        });
    }
    function hideAllSuggestions() {
        const suggestions = document.querySelectorAll(".suggestions");
        for (const suggestion of suggestions) {
            suggestion.innerHTML = "";
        }
    }

    for (const input of filterInputs) {
        input.addEventListener("input", function () {
            const filterType = this.getAttribute("name");
            if (
                filterType === "teacher" ||
                filterType === "room" ||
                filterType === "subject" ||
                filterType === "group"
            ) {
                const query = this.value;
                clearTimeout(suggestionTimeouts[filterType]);
                const inputElement = this;
                suggestionTimeouts[filterType] = setTimeout(() => {
                    fetchSuggestions(filterType, query, inputElement);
                }, 300);
            }
        });
        input.addEventListener("focus", function () {
            const filterType = this.getAttribute("name");
            if (
                filterType === "teacher" ||
                filterType === "room" ||
                filterType === "subject" ||
                filterType === "group"
            ) {
                const query = this.value;
                fetchSuggestions(filterType, query, this);
            }
        });
        input.addEventListener("blur", function () {
            setTimeout(() => {
                hideAllSuggestions();
            }, 100);
        });
    }
    for (const el of searchBtn) {
        el.addEventListener("click", () => {
            fetchSchedule();
            hideAllSuggestions();
        });
    }
    for (const el of filterInputs) {
        el.addEventListener("change", () => fetchSchedule());
    }


    function loadFiltersFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const teacher = urlParams.get("teacher");
        const room = urlParams.get("room");
        const group = urlParams.get("group");
        const subject = urlParams.get("query");
        const albumNumber = urlParams.get("id");

        if (teacher) {
            document.querySelector('input[name="teacher"]').value = teacher;
        }
        if (room) {
            document.querySelector('input[name="room"]').value = room;
        }
        if (group) {
            document.querySelector('input[name="group"]').value = group;
        }
        if (subject) {
            document.querySelector('input[name="subject"]').value = subject;
        }
        if (albumNumber) {
            document.querySelector('input[name="albumNumber"]').value = albumNumber;
        }
        const startParam = urlParams.get("start");
        if (startParam) {
            try {
                const startDate = new Date(startParam.split("T")[0]);
                if (!isNaN(startDate)) {
                    currentDate = startDate;
                }
            } catch (e) {
                console.error("Error parsing start parameter from url", e);
            }
        }
    }

    loadFiltersFromUrl();
    initializeCalendar();
    fetchSchedule();
    renderFavoriteLinks();
});