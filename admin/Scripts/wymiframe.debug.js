var debugEnabled = (document.location.hostname.indexOf('local') !=-1); function _g(logData){if (debugEnabled){if (typeof console !== 'undefined'){console.group(logData);}}return false;}function _u(){if (debugEnabled){if (typeof console !== 'undefined'){console.groupEnd();	}}	return false;}function _d(logData){if (debugEnabled){	if (typeof console !== 'undefined'){console.log(logData);}}	return false;}