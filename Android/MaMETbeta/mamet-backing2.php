package com.example.mametbeta;


import java.util.ArrayList;
import java.util.List;
import java.text.SimpleDateFormat;
import java.util.Calendar;

import org.apache.commons.lang3.StringUtils;
import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;

import android.annotation.SuppressLint;

import java.util.Locale;
import java.io.IOException;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.location.LocationListener;
import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;

import android.os.Bundle;
import android.os.Handler;
import android.os.PowerManager;
import android.speech.RecognitionListener;
import android.speech.RecognizerIntent;
import android.speech.SpeechRecognizer;
import android.util.Log;
import android.view.Menu;
import android.speech.tts.TextToSpeech;
import android.telephony.SmsManager;
import android.widget.TextView;

@SuppressLint("SimpleDateFormat")
public class Mbeta extends Activity {
		
	String ID;
	String password;
	
	String phoneNumber = "085728755848";
	boolean traffic;
	
	//Location loc;
	//double latitude = location.getLatitude();
	//double longitude = location.getLongitude();
	String latit = "-6.893211";
	String longit = "107.610539";
	
	private static final String TAG = Mbeta.class.getName();

	//wakelock to keep screen on
	protected PowerManager.WakeLock mWakeLock;
	
	//speach recognizer for callbacks
	private SpeechRecognizer mSpeechRecognizer;

	//handler to post changes to progress bar
	private Handler mHandler = new Handler();

	//ui textview
	TextView responseText;

	//intent for speech recogniztion
	Intent mSpeechIntent;
	
	//this bool will record that it's time to kill P.A.L.
	boolean killCommanded = false;

	//legel commands
	private static final String[] VALID_COMMANDS = {
		"begin simulation",
		"yes i am okay",
		"no i am not",
		"where am i",
		"exit"
	};
	private static final int VALID_COMMANDS_SIZE = VALID_COMMANDS.length;

	static final String[] texts = {
		"are you okay", 
		"fine then be careful",
		"accident confirmed.  Begin message transmission",
		"bandung"
	};
	TextToSpeech tts;
	

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_mbeta);

		LocationManager locationManager;
		String context = Context.LOCATION_SERVICE;
		locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
		
		Criteria criteria = new Criteria();
		criteria.setAccuracy(Criteria.ACCURACY_FINE);
		criteria.setAltitudeRequired(false);
		criteria.setBearingRequired(false);
		criteria.setCostAllowed(true);
		criteria.setPowerRequirement(Criteria.POWER_LOW);
		String provider = locationManager.getBestProvider(criteria, true);
		
		Location location =  locationManager.getLastKnownLocation(provider);
		
		locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
		
	}

	@Override
	public void onStart() {
		LocationManager locationManager;
		String context = Context.LOCATION_SERVICE;
		locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
		
		Criteria criteria = new Criteria();
		criteria.setAccuracy(Criteria.ACCURACY_FINE);
		criteria.setAltitudeRequired(false);
		criteria.setBearingRequired(false);
		criteria.setCostAllowed(true);
		criteria.setPowerRequirement(Criteria.POWER_LOW);
		String provider = locationManager.getBestProvider(criteria, true);
		
		Location location =  locationManager.getLastKnownLocation(provider);
		
		locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
		
		mSpeechRecognizer = SpeechRecognizer.createSpeechRecognizer(Mbeta.this);
		SpeechListener mRecognitionListener = new SpeechListener();
		mSpeechRecognizer.setRecognitionListener(mRecognitionListener);
		mSpeechIntent = new Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH);

		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_CALLING_PACKAGE,"com.example.voicecommand");

		// Given an hint to the recognizer about what the user is going to say
		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL,
				RecognizerIntent.LANGUAGE_MODEL_FREE_FORM);

		// Specify how many results you want to receive. The results will be sorted
		// where the first result is the one with higher confidence.
		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_MAX_RESULTS, 20);

		mSpeechIntent.putExtra(RecognizerIntent.EXTRA_PARTIAL_RESULTS, true);

		//aqcuire the wakelock to keep the screen on until user exits/closes app
		final PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
		this.mWakeLock = pm.newWakeLock(PowerManager.SCREEN_DIM_WAKE_LOCK, TAG);
		this.mWakeLock.acquire();
		mSpeechRecognizer.startListening(mSpeechIntent);
		super.onStart();
	}
	
	private String updateWithNewLocation(Location location) {
		String latLongString;
		
		if (location != null) {
			double lat = location.getLatitude();
			double lng = location.getLongitude();
			latLongString = "Lat : " + lat + "\nLong: " + lng;
		} else {
			latLongString = "<Lokasi tidak ditemukan>";
		}
		return latLongString;
	}
	
	private String updateWithNewAddress(Location location) {
		String addressString = "<Alamat tidak ditemukan>";
		
		if (location != null) {
			double lat = location.getLatitude();
			double lng = location.getLongitude();

			Geocoder gc = new Geocoder(this, Locale.getDefault());
			try {
				List<Address> addresses = gc.getFromLocation(lat, lng, 1);
				StringBuilder sb = new StringBuilder();
				if(addresses.size()>0) {
					Address address = addresses.get(0);
					
					for (int i=0; i<address.getMaxAddressLineIndex();i++)
						sb.append(address.getAddressLine(i)).append("\n");
						sb.append(address.getLocality()).append("\n");
						sb.append(address.getPostalCode()).append("\n");
						sb.append(address.getCountryName());
				}
				addressString = sb.toString();
			} catch (IOException e) {};
		} 
		return addressString;
	}
	
	private final LocationListener locationListener = new LocationListener() {
		public void onLocationChanged(Location location) {
			updateWithNewLocation(location);
		}
		
		public void onProviderDisabled(String provider) { 
			updateWithNewLocation(null);
		}
		
		public void onProviderEnabled(String provider) { }
		public void onStatusChanged(String provider, int Status, Bundle extras) { }
		
	};
	
	
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		return false;
	}
	
	public void insert() {
		ArrayList<NameValuePair> postParameters = new ArrayList<NameValuePair>();
		postParameters.add(new BasicNameValuePair("latitude",latit));
		postParameters.add(new BasicNameValuePair("longitude",longit));
		String response = null;
		
		try{
			response = CustomHttpClient.executeHttpPost("localhost/mamet/kirim.php", postParameters);
			String res = response.toString();
			res = res.trim();
			res = res.replaceAll("\\s+", "");
			if(res.equals("1")) Log.e("Data Tersimpan", "simpan");
			else Log.e("Data tersimpan ke server", "server");
			
		}
		
		catch (Exception e) {
			Log.i("yeah", "wooho");
		}
		
	}
	
	String SENT = "SMS_SENT";
	String DELIVERED = "SMS_DELIVERED";
	public String getResponse(int command){
		Calendar c = Calendar.getInstance();
		
		SimpleDateFormat dfDate_day;
		String retString = "I'm sorry, sir. I'm afraid I can't do that.";
		PendingIntent sentPI = PendingIntent.getBroadcast(this, 0, new Intent(SENT), 0);
		PendingIntent deliveredPI = PendingIntent.getBroadcast(this, 0, new Intent(DELIVERED), 0);
		switch (command) {
		case 0:
			retString = texts[0];
			break;
		case 1:
			retString = texts[1];
			break;
		case 2:
			retString = texts[2];
			dfDate_day= new SimpleDateFormat("HH:mm:ss dd/MM/yyyy");
			LocationManager locationManager;
			String context = Context.LOCATION_SERVICE;
			locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);
			
			Criteria criteria = new Criteria();
			criteria.setAccuracy(Criteria.ACCURACY_FINE);
			criteria.setAltitudeRequired(false);
			criteria.setBearingRequired(false);
			criteria.setCostAllowed(true);
			criteria.setPowerRequirement(Criteria.POWER_LOW);
			String provider = locationManager.getBestProvider(criteria, true);
			
			Location location =  locationManager.getLastKnownLocation(provider);
			
			locationManager.requestLocationUpdates(provider, 2000, 10, locationListener);
			insert();
			String latLong = updateWithNewLocation(location);
			String address = updateWithNewAddress(location);
			TextView myLocation = (TextView)findViewById(R.id.GPSLocation);
			myLocation.setText(latLong + "\n" + address);
			String message = "Pada waktu " + dfDate_day.format(c.getTime()) + " di" + address 
					+ "dengan koordinat GPS:" + latLong + "terjadi kecelakaan yang menimpa pengguna nomor ini.";
			SmsManager sms = SmsManager.getDefault();
			sms.sendTextMessage(phoneNumber, null, message, sentPI, deliveredPI);
			break;
		case 3:
			retString = texts[3];
			break;
		case 4:
			killCommanded = true;
			break;

		default:
			break;
		}
		return retString;
	}
	

	protected void onResume(){
		super.onResume();
		tts = new TextToSpeech(Mbeta.this, new TextToSpeech.OnInitListener() {
			@Override
			public void onInit(int status) {
				 //TODO Auto-generated method stub
				if(status != TextToSpeech.ERROR){
					tts.setLanguage(Locale.ENGLISH);
				}
			}
		});
	}
	
	@Override
	protected void onPause() {
		//kill the voice recognizer
		if(mSpeechRecognizer != null){
			mSpeechRecognizer.destroy();
			mSpeechRecognizer = null;
		}
		if(tts != null){
			tts.stop();
			tts.shutdown();
		}
	
		this.mWakeLock.release();
		super.onPause();
	}
	

	private void processCommand(ArrayList<String> matchStrings){
		String response = "I'm sorry, sir. I'm afraid I can't do that.";
		int maxStrings = matchStrings.size();
		boolean resultFound = false;
		for(int i =0; i < VALID_COMMANDS_SIZE && !resultFound;i++){
			for(int j=0; j < maxStrings && !resultFound; j++){
				if(StringUtils.getLevenshteinDistance(matchStrings.get(j), VALID_COMMANDS[i]) <(VALID_COMMANDS[i].length() / 3) ){
					response = getResponse(i);
				}
			}
		}
		
		final String finalResponse = response;
		mHandler.post(new Runnable() {
			public void run() {
				//responseText.setText(finalResponse);
				tts.speak(finalResponse,TextToSpeech.QUEUE_FLUSH, null);
			}
		});

	}
	class SpeechListener implements RecognitionListener {
		public void onBufferReceived(byte[] buffer) {
			Log.d(TAG, "buffer received ");
		}
		public void onError(int error) {
			//if critical error then exit
			if(error == SpeechRecognizer.ERROR_CLIENT || error == SpeechRecognizer.ERROR_INSUFFICIENT_PERMISSIONS){
				Log.d(TAG, "client error");
			}
			//else ask to repeats
			else{
				Log.d(TAG, "other error");
				mSpeechRecognizer.startListening(mSpeechIntent);
			}
		}
		public void onEvent(int eventType, Bundle params) {
			Log.d(TAG, "onEvent");
		}
		public void onPartialResults(Bundle partialResults) {
			Log.d(TAG, "partial results");
		}
		public void onReadyForSpeech(Bundle params) {
			Log.d(TAG, "on ready for speech");
		}
		public void onResults(Bundle results) {
			Log.d(TAG, "on results");
			ArrayList<String> matches = null;
			if(results != null){
				matches = results.getStringArrayList(SpeechRecognizer.RESULTS_RECOGNITION);
				if(matches != null){
					Log.d(TAG, "results are " + matches.toString());
					final ArrayList<String> matchesStrings = matches;
					processCommand(matchesStrings);
					if(!killCommanded)
						mSpeechRecognizer.startListening(mSpeechIntent);
					else
						finish();

				}
			}

		}
		public void onRmsChanged(float rmsdB) {
			//			Log.d(TAG, "rms changed");
		}
		public void onBeginningOfSpeech() {
			Log.d(TAG, "speach begining");
		}
		public void onEndOfSpeech() {
			Log.d(TAG, "speach done");
		}

	};

}