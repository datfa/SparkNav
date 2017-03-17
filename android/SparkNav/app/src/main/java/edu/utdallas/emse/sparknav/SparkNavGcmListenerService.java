package edu.utdallas.emse.sparknav;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Intent;
import android.graphics.BitmapFactory;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.NotificationCompat;
import android.util.Log;

import com.google.android.gms.gcm.GcmListenerService;

/**
 * Created by samiul on 3/8/17.
 */

public class SparkNavGcmListenerService extends GcmListenerService {

    private static final String TAG = "GCM_EXERCISE_GLS";
    private static final int NOTIFY_ID = 1001;

    @Override
    public void onMessageReceived(String from, Bundle data) {
        String message = data.getString("message");

        if (from.startsWith("/topics/")) {
            Log.d(TAG, "Received a topic broadcast");
            // message received from some topic.
            createNotification();
        }
        else {
            Log.d(TAG, "Received a downstream message");
            // normal downstream message.
        }
        Log.d(TAG, "From: " + from);
        Log.d(TAG, "Message: " + message);
    }

    private void createNotification() {
        // create the NotificationCompat Builder
        NotificationCompat.Builder builder = new NotificationCompat.Builder(this);

        // Create the intent that will start the ResultActivity when the user
        // taps the notification or chooses an action button
        //Intent intent = new Intent(getApplicationContext(), NotificationResultActivity.class);
        Intent intent = new Intent(this, NotificationResultActivity.class);
        // Store the notification ID so we can cancel it later in the ResultActivity
        intent.putExtra("notifyID", NOTIFY_ID);
        //PendingIntent pendingIntent = PendingIntent.getActivity(getApplicationContext(), NOTIFY_ID, intent, PendingIntent.FLAG_CANCEL_CURRENT);
        PendingIntent pendingIntent = PendingIntent.getActivity(this, NOTIFY_ID, intent, PendingIntent.FLAG_CANCEL_CURRENT);

        // Set the three required items all notifications must have
        builder.setSmallIcon(R.drawable.ic_stat_sample_notification); //if you want an icon
        builder.setContentTitle("Attention Required");
        builder.setContentText("UTD Emergency aleart!!");

        // Set the notification to cancel when the user taps on it
        builder.setAutoCancel(true);

        // Set the large icon to be our app's launcher icon
        builder.setLargeIcon(BitmapFactory.decodeResource(getResources(), R.mipmap.ic_launcher));

        // Set the small subtext message
        builder.setSubText("Tap to view");

        // Set the content intent to launch our result activity
        builder.setContentIntent(pendingIntent);

        // Add an expanded layout to the notification
        NotificationCompat.BigTextStyle bigTextStyle = new NotificationCompat.BigTextStyle();
        bigTextStyle.setBigContentTitle("This is a big notification");
        bigTextStyle.bigText(getResources().getString(R.string.LongMsg));
        builder.setStyle(bigTextStyle);

        // Add action buttons to the Notification if they are supported
        // Use the same PendingIntent as we use for the main notification action
//        builder.addAction(R.mipmap.ic_launcher,"Action 1", pendingIntent);
//        builder.addAction(R.mipmap.ic_launcher,"Action 2", pendingIntent);

        builder.addAction(R.mipmap.ic_launcher,"Start Navigation", pendingIntent);

        // Set the lock screen visibility of the notification
        builder.setVisibility(Notification.VISIBILITY_PUBLIC);

        //Define sound URI
        //Uri soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
        Uri soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
        builder.setSound(soundUri); //This sets the sound to play

        // Build the finished notification and then display it to the user
        Notification notification = builder.build();

        //next line for default notification sound
        //notification.defaults = Notification.DEFAULT_SOUND;

        // use following way for custom sound
        //final String packageName = context.getPackageName();
        //notification.sound = Uri.parse("android.resource://" + packageName + "/" + soundResId);

        NotificationManager mgr = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
        mgr.notify(NOTIFY_ID, notification);

        Log.d(TAG, "====> NOTIFICATION CREATED!!");
    }
}