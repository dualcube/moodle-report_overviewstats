M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.modules = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var instances = new Y.Chart({
            type: "pie",
            categoryKey: "module",
            seriesKeys: ["count"],
            dataProvider: data.instances,
            legend: {
                position: "right",
                width: 300,
                height: 300,
                styles: {
                    hAlign: "center",
                    hSpacing: 4
                }
            },
            seriesCollection:[
                {
                    categoryKey: "module",
                    valueKey: "count"
                }
            ]
        });

        Y.one("#chart_modules_instances").setStyle("backgroundImage", "none");
        instances.render("#chart_modules_instances");

        var usage = new Y.Chart({
            type: "column",
            categoryKey: "indicator",
            dataProvider: data.usage,
            horizontalGridlines: true,
            styles: {
                axes: {
                    indicator: {
                        label: {
                            rotation: -90
                        }
                    }
                }
            }
        });

        Y.one("#chart_modules_usage").setStyle("backgroundImage", "none");
        usage.render("#chart_modules_usage");
    }
};
