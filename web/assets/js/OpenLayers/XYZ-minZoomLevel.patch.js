OpenLayers.Layer.XYZ.prototype.initialize =  function(name, url, options) {
    var minZoom=0;

    if(options.minZoomLevel)
        minZoom=options.minZoomLevel;

    if (options && options.sphericalMercator || this.sphericalMercator) {
        options = OpenLayers.Util.extend({
            projection: "EPSG:900913",
            maxResolution: 156543.03390625/Math.pow(2,minZoom),
            numZoomLevels: 19 - minZoom, 
            numZoomLevel: minZoom
        }, options);
    }
    OpenLayers.Layer.Grid.prototype.initialize.apply(this, [
        name || this.name, url || this.url, {}, options
    ]);
}

