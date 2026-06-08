// Import styles
import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";
import "../css/app.css";

// Import libraries
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

// Import chart components
import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import uploadsBarChart from "./components/charts/uploads-bar-chart";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";

// Import role-specific charts
import dataClerkCharts from "./components/charts/data-clerk-charts";
import marriageTellerCharts from "./components/charts/marriage-teller-charts";
import marriageRegistrarCharts from "./components/charts/marriage-registrar-charts";
import attorneyGeneralCharts from "./components/charts/attorney-general-charts";
import adminCharts from "./components/charts/admin-charts";

// Alpine setup
Alpine.plugin(persist);
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

console.log('Alpine started via index.js');

// Initialize flatpickr
document.addEventListener("DOMContentLoaded", () => {
    // Flatpickr initialization
    flatpickr(".datepicker", {
        mode: "range",
        static: true,
        monthSelectorType: "static",
        dateFormat: "M j, Y",
        defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
        prevArrow: '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        nextArrow: '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        onReady: (selectedDates, dateStr, instance) => {
            instance.element.value = dateStr.replace("to", "-");
            const customClass = instance.element.getAttribute("data-class");
            if (customClass) {
                instance.calendarContainer.classList.add(customClass);
            }
        },
        onChange: (selectedDates, dateStr, instance) => {
            instance.element.value = dateStr.replace("to", "-");
        },
    });

    // Initialize Dropzone
    const dropzoneArea = document.querySelectorAll("#demo-upload");
    if (dropzoneArea.length) {
        new Dropzone("#demo-upload", { url: "/file/post" });
    }
});

// Initialize all charts with a slight delay to ensure DOM is ready
setTimeout(() => {
    console.log("Initializing charts...");
    try {
        // Initialize base charts
        chart01();
        chart02();
        chart03();
        uploadsBarChart();
        map01();
        
        // Initialize role-specific charts based on user role
        const userRoleElement = document.getElementById('user-role-data');
        const userRole = userRoleElement ? userRoleElement.dataset.role : null;
        
        console.log("User role detected:", userRole);
        
        // Initialize role-specific charts
        switch (userRole) {
            case 'data_clerk':
                console.log("Initializing Data Clerk charts...");
                if (typeof dataClerkCharts === 'function') {
                    dataClerkCharts();
                } else {
                    console.error("dataClerkCharts is not a function");
                }
                break;
            case 'marriage_teller':
                console.log("Initializing Marriage Teller charts...");
                if (typeof marriageTellerCharts === 'function') {
                    marriageTellerCharts();
                } else {
                    console.error("marriageTellerCharts is not a function");
                }
                break;
            case 'marriage_registrar':
                console.log("Initializing Marriage Registrar charts...");
                if (typeof marriageRegistrarCharts === 'function') {
                    marriageRegistrarCharts();
                } else {
                    console.error("marriageRegistrarCharts is not a function");
                }
                break;
            case 'attorney_general':
            case 'ag':
                console.log("Initializing Attorney General charts...");
                if (typeof attorneyGeneralCharts === 'function') {
                    attorneyGeneralCharts();
                } else {
                    console.error("attorneyGeneralCharts is not a function");
                }
                break;
            case 'admin':
                console.log("Initializing Admin charts...");
                if (typeof adminCharts === 'function') {
                    adminCharts();
                } else {
                    console.error("adminCharts is not a function");
                }
                break;
            default:
                console.log("No role-specific charts for:", userRole);
        }
        
        console.log("All charts initialized successfully");
    } catch (error) {
        console.error("Error initializing charts:", error);
    }
}, 200);

// ============================================
// EXPORT FUNCTIONS FOR MAIN UPLOADS CHART
// ============================================
window.exportChart = function(type) {
    if (!window.uploadChart) {
        console.error("Chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#uploadsBarChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.uploadChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            if (type === "png") {
                a.href = imgURI;
                a.download = "pdf-pages-chart.png";
            } else {
                a.href = svgURI;
                a.download = "pdf-pages-chart.svg";
            }
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const dates = JSON.parse(chartElement.dataset.dates || "[]");
            const counts = JSON.parse(chartElement.dataset.counts || "[]");

            let csv = "Date,PDF Pages\n";
            dates.forEach((date, i) => {
                csv += `${date},${counts[i]}\n`;
            });

            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "pdf-pages-data.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// ============================================
// EXPORT FUNCTIONS FOR MARRIAGE TELLER CHARTS
// ============================================
window.exportClerkChart = function(type) {
    if (!window.clerkPerformanceChart) {
        console.error("Clerk performance chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#clerkPerformanceChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.clerkPerformanceChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `clerk-performance.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const clerks = JSON.parse(chartElement.dataset.clerks || "[]");
            let csv = "Clerk Name,Assigned Pages,Completed Pages,Review Needed,Completion Rate (%)\n";
            clerks.forEach((clerk) => {
                csv += `"${clerk.name}",${clerk.assigned},${clerk.completed},${clerk.review_needed || 0},${clerk.completion_rate}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "clerk-performance.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportTrendChart = function(type) {
    if (!window.weeklyTrendChart) {
        console.error("Weekly trend chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#weeklyTrendChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.weeklyTrendChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `weekly-trend.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const trend = JSON.parse(chartElement.dataset.trend || "[]");
            let csv = "Week,Pages Completed\n";
            trend.forEach((item) => {
                csv += `${item.week},${item.count}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "weekly-trend.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// ============================================
// EXPORT FUNCTIONS FOR MARRIAGE REGISTRAR CHARTS
// ============================================
window.exportVerificationQueueChart = function(type) {
    if (!window.verificationQueueChart) {
        console.error("Verification queue chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#verificationQueueChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.verificationQueueChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `verification-queue.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const queue = JSON.parse(chartElement.dataset.queue || "{}");
            let csv = "Status,Pages\n";
            for (const [status, count] of Object.entries(queue)) {
                csv += `${status.replace(/_/g, ' ').toUpperCase()},${count}\n`;
            }
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "verification-queue.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportPagesByTypeChart = function(type) {
    if (!window.pagesByTypeChart) {
        console.error("Pages by type chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#pagesByTypeChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.pagesByTypeChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `pages-by-type.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const types = JSON.parse(chartElement.dataset.types || "{}");
            let csv = "Marriage Type,Pages\n";
            for (const [typeName, count] of Object.entries(types)) {
                csv += `${typeName},${count}\n`;
            }
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "pages-by-type.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportVerificationTrendChart = function(type) {
    if (!window.verificationTrendChart) {
        console.error("Verification trend chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#verificationTrendChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.verificationTrendChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `verification-trend.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const trend = JSON.parse(chartElement.dataset.trend || "[]");
            let csv = "Date,Verifications\n";
            trend.forEach((item) => {
                csv += `${item.label},${item.count}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "verification-trend.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// ============================================
// EXPORT FUNCTIONS FOR DATA CLERK CHARTS
// ============================================
window.exportStatusChart = function(type) {
    if (!window.statusChart) {
        console.error("Status chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#statusDistributionChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.statusChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `status-distribution.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const statuses = JSON.parse(chartElement.dataset.statuses || "[]");
            const values = JSON.parse(chartElement.dataset.values || "[]");
            
            let csv = "Status,Pages\n";
            statuses.forEach((status, i) => {
                const formattedStatus = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                csv += `${formattedStatus},${values[i]}\n`;
            });
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "status-distribution.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// Export Top Clerks Table as CSV
window.exportTopClerksTable = function() {
    try {
        const table = document.querySelector('#top-clerks-table');
        if (table) {
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (const row of rows) {
                const rowData = [];
                const cols = row.querySelectorAll('td, th');
                for (const col of cols) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                }
                csv.push(rowData.join(','));
            }
            
            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'top-clerks.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        }
    } catch (e) {
        console.error("Error exporting CSV:", e);
        alert("Error exporting CSV. Please try again.");
    }
};

window.exportProductivityChart = function(type) {
    if (!window.productivityChart) {
        console.error("Productivity chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#dailyProductivityChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.productivityChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `daily-productivity.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const labels = JSON.parse(chartElement.dataset.labels || "[]");
            const counts = JSON.parse(chartElement.dataset.counts || "[]");
            
            let csv = "Date,Pages Completed\n";
            labels.forEach((label, i) => {
                csv += `${label},${counts[i]}\n`;
            });
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "daily-productivity.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// ============================================
// EXPORT FUNCTIONS FOR ADMIN CHARTS
// ============================================
window.exportUsersByRoleChart = function(type) {
    if (!window.usersByRoleChart) {
        console.error("Users by role chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#usersByRoleChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.usersByRoleChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `users-by-role.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const roles = JSON.parse(chartElement.dataset.roles || "[]");
            let csv = "Role,Users\n";
            roles.forEach((role) => {
                csv += `${role.role},${role.count}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "users-by-role.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportActivityTimelineChart = function(type) {
    if (!window.activityTimelineChart) {
        console.error("Activity timeline chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#activityTimelineChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.activityTimelineChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `activity-timeline.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const activity = JSON.parse(chartElement.dataset.activity || "[]");
            let csv = "Date,Uploads,Marriages\n";
            activity.forEach((item) => {
                csv += `${item.label},${item.uploads},${item.marriages}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "activity-timeline.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportUploadTrendsChart = function(type) {
    if (!window.uploadTrendsChart) {
        console.error("Upload trends chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#uploadTrendsChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.uploadTrendsChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `upload-trends.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const trends = JSON.parse(chartElement.dataset.trends || "[]");
            let csv = "Month,Uploads,Storage (MB)\n";
            trends.forEach((item) => {
                csv += `${item.month},${item.uploads},${item.size_mb}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "upload-trends.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportTopClerksChart = function(type) {
    if (!window.topClerksChart) {
        console.error("Top clerks chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#topClerksChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.topClerksChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `top-clerks.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const names = JSON.parse(chartElement.dataset.names || "[]");
            const rates = JSON.parse(chartElement.dataset.rates || "[]");
            let csv = "Clerk Name,Completion Rate (%)\n";
            names.forEach((name, i) => {
                csv += `${name},${rates[i]}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "top-clerks.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// ============================================
// EXPORT FUNCTIONS FOR ATTORNEY GENERAL CHARTS
// ============================================
window.exportCountiesVsPagesChart = function(type) {
    if (!window.countiesChart) {
        console.error("Counties vs pages chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#countiesVsPagesChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.countiesChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `counties-vs-pages.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const counties = JSON.parse(chartElement.dataset.counties || "[]");
            let csv = "County,Pages\n";
            counties.forEach((item) => {
                csv += `${item.county},${item.pages}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "counties-vs-pages.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportMarriageTypesChart = function(type) {
    if (!window.typesChart) {
        console.error("Marriage types chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#marriageTypesDistributionChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.typesChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `marriage-types.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const types = JSON.parse(chartElement.dataset.types || "[]");
            let csv = "Marriage Type,Count\n";
            types.forEach((item) => {
                csv += `${item.type},${item.count}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "marriage-types.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportYearsTrendChart = function(type) {
    if (!window.yearsTrendChart) {
        console.error("Years trend chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#yearsTrendChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.yearsTrendChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `years-trend.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const years = JSON.parse(chartElement.dataset.years || "[]");
            let csv = "Year,Uploads,Pages\n";
            years.forEach((item) => {
                csv += `${item.year},${item.uploads},${item.pages}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "years-trend.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

window.exportCountyMarriagesChart = function(type) {
    if (!window.countyMarriagesChart) {
        console.error("County marriages chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#countyMarriagesChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.countyMarriagesChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `county-marriages.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const marriages = JSON.parse(chartElement.dataset.marriages || "[]");
            let csv = "County,Marriages\n";
            marriages.forEach((item) => {
                csv += `${item.county},${item.marriages}\n`;
            });
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "county-marriages.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// Update year
const yearElement = document.getElementById("year");
if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
}

console.log("✅ index.js fully loaded");