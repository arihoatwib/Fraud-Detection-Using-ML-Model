<h1 align='center'>💥 Online Payments Fraud Detection Machine Learning Model 💥</h1>

<h3>:zap: GOAL</h3>

- The aim of the project is to analyze and predict whether transactions are either **Fraud** or **No Fraud**.

### :zap: **DATASET** 

- https://www.kaggle.com/ealaxi/paysim1

<div align='center'>

### :zap: **TECH STACK USED**

<a href="https://jupyter.org/" rel="noreferrer"> <img src="https://img.shields.io/badge/Jupyter-F37626.svg?&style=for-the-badge&logo=Jupyter&logoColor=white" alt="jupyter-notebook" /> </a>
<a href="https://https://numpy.pydata.org/" rel="noreferrer"> <img src="https://img.shields.io/badge/Numpy-777BB4?style=for-the-badge&logo=numpy&logoColor=white" alt="numpy" /> </a>
<a href="https://pandas.pydata.org/" rel="noreferrer"> <img src="https://img.shields.io/badge/Pandas-2C2D72?style=for-the-badge&logo=pandas&logoColor=white" alt="pandas" /> </a>
<a href="https://matplotlib.org/" rel="noreferrer"> <img src="https://img.shields.io/badge/Matplotlib-%23ffffff.svg?style=for-the-badge&logo=Matplotlib&logoColor=black" alt="matplotlib" /> </a>
<a href="https://scikit-learn.org/stable/" rel="noreferrer"> <img src="https://img.shields.io/badge/scikit_learn-F7931E?style=for-the-badge&logo=scikit-learn&logoColor=white" alt="scikit-learn" /> </a>
<a href="https://flask.palletsprojects.com/en/3.0.x/" rel="noreferrer"> <img src="https://img.shields.io/badge/flask-%23000.svg?style=for-the-badge&logo=flask&logoColor=white" alt="flask" /> </a>
<a href="https://www.w3schools.com/html/" rel="noreferrer"> <img src="https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white" alt="html5" /> </a>
<a href="https://www.w3schools.com/css/" rel="noreferrer"> <img src="https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white" alt="css3" /> </a>
<a href="https://console.cloud.google.com/welcome?project=superb-tendril-373416" rel="noreferrer"> <img src="https://img.shields.io/badge/GoogleCloud-%234285F4.svg?style=for-the-badge&logo=google-cloud&logoColor=white" alt="google-cloud" /> </a>

</div>

### :zap: **DESCRIPTION**

To analyze the dataset of the Online Payments Fraud Detection Dataset and build and train the model on the basis of different features and variables.

There are 11 features and 6362620 entries in this dataset.

- **`step`**: Maps a unit of time in the real world. In this case 1 step is 1 hour of time. Total steps 744 (30 days simulation).

- **`type`**: CASH-IN, CASH-OUT, DEBIT, PAYMENT and TRANSFER.

- **`amount`**: Amount of the transaction in local currency.

- **`nameOrig`**: Customer who started the transaction.

- **`oldbalanceOrg`**: Initial balance before the transaction.

- **`newbalanceOrig`**: New balance after the transaction.

- **`nameDest`**: Customer who is the recipient of the transaction.

- **`oldbalanceDest`**: Initial balance recipient before the transaction. Note that there is not information for customers that start with M (Merchants).

- **`newbalanceDest`**: New balance recipient after the transaction. Note that there is not information for customers that start with M (Merchants).

- **`isFraud`**: This is the transactions made by the fraudulent agents inside the simulation. In this specific dataset the fraudulent behavior of the agents aims to profit by taking control or customers accounts and try to empty the funds by transferring to another account and then cashing out of the system.

- **`isFlaggedFraud`**: The business model aims to control massive transfers from one account to another and flags illegal attempts. An illegal attempt in this dataset is an attempt to transfer more than 200.000 in a single transaction.

### :zap: **LIBRARIES NEEDED**

1. Pandas
2. Numpy
3. Matplotlib
4. Sklearn
5. Sci-py
6. Seaborn
7. Joblib
8. Flask


### :zap: **HOW TO USE IT**

* Create a virtual environment using `python -m venv myenv`.
* To activate the virtual environment use `.\myenv\Scripts\activate`.
* If error occurs, use `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`.
* Now, app.py is the flask app code. run the command "pip install -r requirements.txt" to install the required dependencies for the flask app.
* You may need to install additional libraries for running the jupyter notebooks.


### :zap: **WHAT I HAVE DONE**

* Load the dataset which contains 6362620 entries in it and having 11 features in it.
* Performing EDA on the dataset to get insights of the dataset.
* Plotting different features graphs correspond to `target` feature.
* Analyse the dataset by using correlation and plot the bar plot i.e., how much it is related to `target` feature.
* Reduce the parameters and split the dataset into input and target features.
* Split the parameters into training and testing sets.
* Train the different models and get their accuracies and MSE & R2 scores even after tuning the hyper-parameters.
* Even build a neural network and tune the parameters of their.
* But Decision Tree Classifier Model gives promising performance on this dataset and classify and fit to the target variable with upto 99.97%.
* Save the model into `.joblib` extension file and create a front-end for it.
* Also creating a `requirements.txt` file for the model and website build-up.
* Create a front-end using **FLASK** framework and create a user-friendly template.
* Website can takes input and pass to the backend of the model and model will predict and provide the user a best result as of accuracy is around ***99.80%***.
* The user can also upload a CSV file of transactions then the transactions are saved in the database, then after the user can click on the predict button to predict the fraud transactions amongst those the user uploaded in the database



### :zap: **Visualization and EDA of different attributes**

<table align='center'>
  <tr align='center'>
    <td align='center'>
      <img alt="graph" src="/static/images/pie_chart.png" >
    </td>
    <td align='center'>
      <img alt="graph" src="/static/images/target_correlation.png" >
    </td>
  </tr>

  <tr align='center'>
    <td align='center'>
      <img alt="heatmap" src="/static/images/correlation_heatmap.png" >
    </td>
    <td align='center'>
      <img alt="graph" src="/static/images/type_feature.png" >
    </td>
  </tr>

  <tr align='center'>
    <td align='center'>
      <img alt="graph" src="/static/images/amount_feature.png" >
    </td>
    <td align='center'>
      <img alt="graph" src="/static/images/oldbalanceOrg_feature.png" >
    </td>
  </tr>

  <tr align='center'>
    <td align='center'>
      <img alt="graph" src="/static/images/newbalanceOrig_feature.png" >
    </td>
    <td align='center'>
      <img alt="graph" src="/static/images/oldbalanceDest_feature.png" >
    </td>
  </tr>

  <tr align='center'>
    <td align='center'>
      <img alt="graph" src="/static/images/newbalanceDest_feature.png" >
    </td>
    <td align='center'>
      <img alt="graph" src="/static/images/isFlaggedFraud_feature.png" >
    </td>
  </tr>
</table>


### :zap: **CONCLUSION**

- XGBoost Classifier models show outstanding performance with **99.80%** accuracy of the model.
- Created a user-friendly front-end framework using **FLASK** and integrate it to the model.

#### :zap: **Outputs**

<table align='center'>
  <tr align='center'>
    <td align='center'>
      <img alt='Fraud' src='/static/images/Fraud.png' >
    </td>
    <td align='center'>
      <img alt='No-Fraud' src='/static/images/No-Fraud.png' >
    </td>
  </tr>
</table>