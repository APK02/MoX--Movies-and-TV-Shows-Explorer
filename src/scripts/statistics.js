console.log("Statistics page loaded");
document.addEventListener("DOMContentLoaded", () => {
    //bar chart
    const movies = JSON.parse(localStorage.getItem('movies')) || [];
    let genreCounts = {};

    movies.forEach((movie) => {
        movie.genres.forEach((genre) => {
            if (!genreCounts[genre]) {
                genreCounts[genre] = 1;
            } else {
                genreCounts[genre]++;
            }
        });
    });

    const genres = Object.keys(genreCounts);
    const counts = Object.values(genreCounts);

    const backgroundColors = genres.map(() => `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.5)`);

    const chart1 = document.getElementById('myChart1');
                
    new Chart(chart1, {
        type: 'bar',
        data: {
            labels: genres,
            datasets: [{
                label: '# of Movies',
                data: counts,
                borderWidth: 1,
                backgroundColor: backgroundColors,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value.toString(); 
                            }
                            return '';
                        }
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Movies by Genre',
                    font: {
                        size: 20
                    }
                }
            }
        }
    });

    //csv
    globalThis.csv1 = 'Title,Genres\n';
    movies.forEach((movie) => {
        csv1 += `${movie.title},"${movie.genres.join(', ')}"\n`;
    })

    //pie chart
    const scores = JSON.parse(localStorage.getItem('scores')) || [];
    const scoreCounts = new Array(10).fill(0);
    scores.forEach(score => {
        const index = Math.floor(score);
        if (index < 10) {
            scoreCounts[index]++;
        }
    })

    const backgroundColors2 = scoreCounts.map(() => `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.5)`);
    
    const data = {
        labels: scoreCounts.map((_, index) => `${index}.0 - ${index + 1}.0`),
        datasets: [{
            label: 'Scores Distribution',
            data: scoreCounts,
            backgroundColor: backgroundColors2,
            borderWidth: 1
        }]
    }

    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Movie Scores by range',
                    font: {
                        size: 20
                    }
                }
            }
        }
    }
    
    const chart2 = document.getElementById('myChart2');
    new Chart(chart2, config);

    //csv
    globalThis.csv2 = 'Title,Score\n';
    let i = 0;
    movies.forEach((movie) => {
        csv2 += `${movie.title},${scores[i]}\n`;
        i++;
    })

    //radar chart
    let ratings = [];
    movies.forEach((movie) => {
        if (!ratings.includes(movie.rating)) {
            ratings.push(movie.rating);
        }
    });

    let ratingCountsNetflix = {};
    let ratingCountsDisney = {};    
    ratings.forEach((rating) => {
        ratingCountsNetflix[rating] = 0;
        ratingCountsDisney[rating] = 0;
    })
    movies.forEach((movie) => {
        if (movie.streaming_platform === 'Netflix') {
            ratingCountsNetflix[movie.rating]++;
        } else if (movie.streaming_platform === 'Disney') {
            ratingCountsDisney[movie.rating]++;
        }
    })
    const countNetflix = Object.values(ratingCountsNetflix);
    const countDisney = Object.values(ratingCountsDisney);

    const data2 = {
        labels: ratings,
        datasets: [{
            label: 'Netflix',
            data: countNetflix,
            fill: true,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgb(255, 99, 132)',
            pointBackgroundColor: 'rgb(255, 99, 132)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(255, 99, 132)'
        }, {
            label: 'Disney',
            data: countDisney,
            fill: true,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgb(54, 162, 235)',
            pointBackgroundColor: 'rgb(54, 162, 235)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(54, 162, 235)'
        }]
    }

    const config2 = {
        type: 'radar',
        data: data2,
        options: {
            elements: {
                line: {
                    borderWidth: 3
                }
            },
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Ratings by Streaming Platform',
                    font: {
                        size: 20
                    }
                }
            }
        }
    }

    const chart3 = document.getElementById('myChart3');
    new Chart(chart3, config2);

    //csv
    globalThis.csv3 = 'Title,Streaming Platform,Rating\n';
    movies.forEach((movie) => {
        csv3 += `${movie.title},${movie.streaming_platform},${movie.rating}\n`;
    })
});

async function downloadCSV(i) {
    if (i === 1) {
        var hiddenElement = document.createElement('a');
        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv1);
        hiddenElement.target = '_blank';
    
        hiddenElement.download = 'Movies_by_Genre.csv';
        hiddenElement.click();  
    } else if (i === 2) {
        var hiddenElement = document.createElement('a');
        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv2);
        hiddenElement.target = '_blank';
    
        hiddenElement.download = 'Movie_Scores.csv';
        hiddenElement.click();  
    } else if (i === 3) {
        var hiddenElement = document.createElement('a');
        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv3);
        hiddenElement.target = '_blank';
    
        hiddenElement.download = 'Ratings_by_Streaming_Platform.csv';
        hiddenElement.click();  
    }

}

async function downloadSVG(i) {
    let canvas;
    let name;
    if (i === 1) {
        canvas = document.getElementById('myChart1');
        name = "bar_chart.svg";
    } else if (i === 2) {
        canvas = document.getElementById('myChart2');
        name = "pie_chart.svg";
    } else if (i === 3) {
        canvas = document.getElementById('myChart3');
        name = "radar_chart.svg";
    }
    const svg = canvasToSVG(canvas);
    const svgBlob = new Blob([svg], { type: "image/svg+xml;charset=utf-8" });
    const svgUrl = URL.createObjectURL(svgBlob);
    var downloadLink = document.createElement("a");
    downloadLink.href = svgUrl;
    downloadLink.target = "_blank";
    downloadLink.download = name;
    downloadLink.click();
}

function canvasToSVG(canvas) {
    const xmlns = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(xmlns, "svg");
    svg.setAttributeNS(null, "width", canvas.width);
    svg.setAttributeNS(null, "height", canvas.height);
    const img = new Image();
    img.src = canvas.toDataURL("image/png");
    const imgElement = document.createElementNS(xmlns, "image");
    imgElement.setAttributeNS(null, "height", canvas.height);
    imgElement.setAttributeNS(null, "width", canvas.width);
    imgElement.setAttributeNS("http://www.w3.org/1999/xlink", 'href', img.src);
    svg.appendChild(imgElement);
    return new XMLSerializer().serializeToString(svg);
}

async function downloadWebP(i) {
    let canvas;
    let name;
    if (i === 1) {
        canvas = document.getElementById('myChart1');
        name = "bar_chart.webp";
    } else if (i === 2) {
        canvas = document.getElementById('myChart2');
        name = "pie_chart.webp";
    } else if (i === 3) {
        canvas = document.getElementById('myChart3');
        name = "radar_chart.webp";
    }
    const webp = canvas.toDataURL("image/webp");
    var downloadLink = document.createElement("a");
    downloadLink.href = webp;
    downloadLink.target = "_blank";
    downloadLink.download = name;
    downloadLink.click();
}